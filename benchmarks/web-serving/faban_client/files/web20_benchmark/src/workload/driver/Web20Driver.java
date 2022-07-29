package workload.driver;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Random;
import java.util.Timer;
import java.util.TimerTask;
import java.util.concurrent.Semaphore;
import java.util.logging.FileHandler;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.logging.SimpleFormatter;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import javax.sql.rowset.serial.SerialException;
import javax.xml.xpath.XPathExpressionException;

import org.json.JSONObject;

import com.sun.faban.driver.Background;
import com.sun.faban.driver.BenchmarkDefinition;
import com.sun.faban.driver.BenchmarkDriver;
import com.sun.faban.driver.BenchmarkOperation;
import com.sun.faban.driver.CustomMetrics;
import com.sun.faban.driver.CycleType;
import com.sun.faban.driver.DriverContext;
import com.sun.faban.driver.Uniform;
import com.sun.faban.driver.FixedTime;
import com.sun.faban.driver.NegativeExponential;
import com.sun.faban.driver.HttpTransport;
import com.sun.faban.driver.MatrixMix;
import com.sun.faban.driver.OnceAfter;
import com.sun.faban.driver.Row;
import com.sun.faban.driver.Timing;

import workload.driver.RandomStringGenerator.Mode;
import workload.driver.Web20Client.ClientState;

@BenchmarkDefinition(
    name    = "Elgg benchmark",
    version = "1.0"
)

@BenchmarkDriver(
    name             = "ElggDriver",
    threadPerScale   = 1,
    percentiles      = {"95", "99", "99.9"},
    responseTimeUnit = java.util.concurrent.TimeUnit.MILLISECONDS
)

@FixedTime(
    cycleTime      = 100,
    cycleType      = CycleType.THINKTIME,
    cycleDeviation = 2
)

@MatrixMix(
    operations = {"BrowsetoElgg", "DoLogin", "PostSelfWall", "SendChatMessage", "AddFriend", "Register", "Logout"},
    mix        = {@Row({0, 80, 0, 0, 0, 0, 20}), @Row({0, 0, 30, 55, 9, 0, 1}), @Row({0, 0, 30, 55, 9, 0, 1}), @Row({0, 0, 30, 55, 9, 0, 1}), @Row({0, 0, 30, 55, 9, 0, 1}), @Row({80, 20, 0, 0, 0, 0, 0}), @Row({90, 0, 0, 0, 0, 10, 0})}
)

@Background(
    operations = {"ReceiveChatMessage"},
    timings    = {@FixedTime(cycleTime = 1000, cycleType = CycleType.THINKTIME, cycleDeviation = 2)}
)


/**
 * The main driver class.
 *
 * Operations :-
 *
 * Create new user (X)
 * Login existing user (X)
 * Logout logged in user
 * Activate user
 * Wall post (X)
 * New blog post
 * Send friend request (X)
 * Send chat message (X)
 * Receive chat message (X)
 * Update live feed (X)
 * Refresh security token
 *
 * @author Tapti Palit
 *
 */
public class Web20Driver {

	private List<UserPasswordPair> userPasswordList;

	private DriverContext context;
	private Logger logger;
	private FileHandler fileTxt;

	private SimpleFormatter formatterTxt;

	private ElggDriverMetrics elggMetrics;

	private String hostUrl;

	private UserPasswordPair thisUserPasswordPair;

	private Web20Client thisClient;


	private Random random;

	private boolean inited;

	private static Semaphore semaphore;
	private static Timer     timer;

	/* Constants : URL */
	private final String ROOT_URL = "/";

	// a_ansaarii: these urls refer to the old elgg. They do not exist in the current version
	private final String[] ROOT_URLS = new String[] {
			"/vendors/requirejs/require-2.1.10.min.js",
			"/vendors/jquery/jquery-1.11.0.min.js",
			"/vendors/jquery/jquery-migrate-1.2.1.min.js",
			"/vendors/jquery/jquery-ui-1.10.4.min.js",
			"/_graphics/favicon-16.png", "/_graphics/favicon-32.png",
			"/_graphics/icons/user/defaultsmall.gif",
			"/_graphics/icons/user/defaulttiny.gif",
			"/_graphics/header_shadow.png", "/_graphics/elgg_sprites.png",
			"/_graphics/sidebar_background.gif",
			"/_graphics/button_graduation.png", "/_graphics/favicon-128.png" };
	private final String LOGIN_URL = "/action/login";

	// a_ansaarii: these urls refer to the old elgg. They do not exist in the current version
	private final String[] LOGIN_URLS = new String[] { //"/js/lib/ui.river.js",
			"/_graphics/icons/user/defaulttopbar.gif",
//			"/mod/riverautoupdate/_graphics/loading.gif",
			"/_graphics/toptoolbar_background.gif",
			"/mod/reportedcontent/graphics/icon_reportthis.gif" };
	private final String ACTIVITY_URL = "/activity";

	// a_ansaarii: these urls refer to the old elgg. They do not exist in the current version
	private final String[] ACTIVITY_URLS = new String[] {
			"/mod/hypeWall/vendors/fonts/font-awesome.css",
			"/mod/hypeWall/vendors/fonts/open-sans.css" };

	// a_ansaarii: this url refers to the old elgg. They do not exist in the current version
	private final String RIVER_UPDATE_URL = "/activity/proc/updateriver";
	private final String WALL_URL = "/action/wall/status";

	private final String REGISTER_PAGE_URL = "/register";

	private final String DO_REGISTER_URL = "/action/register";
	private final String DO_ADD_FRIEND = "/action/friends/add";

	private final String CHAT_CREATE_URL = "/action/elggchat/create";
	private final String CHAT_POST_URL = "/action/elggchat/post_message";
	private final String CHAT_RECV_URL = "/action/elggchat/poll";

	private final String LEAVE_CHAT_URL = "/action/elggchat/leave";
	private final String LOGOUT_URL = "/action/logout";

	public Web20Driver() throws SecurityException, IOException, XPathExpressionException {

		thisClient = new Web20Client();
		thisClient.setClientState(ClientState.LOGGED_OUT);
		thisClient.setChatSessionList(new ArrayList<String>());

		context = DriverContext.getContext();
		userPasswordList = new ArrayList<UserPasswordPair>();

		logger = context.getLogger();
		logger.setLevel(Level.INFO);

		//fileTxt = new FileHandler("Faban_3log%u.%g.txt", 0, 500);
		//formatterTxt = new SimpleFormatter();
		//fileTxt.setFormatter(formatterTxt);

		/*
		Handler[] handlers = logger.getHandlers();
		List<Handler> toRemoveHandlers = new ArrayList<Handler>();
		for (Handler handler: handlers) {
			toRemoveHandlers.add(handler);
		}
		for (Handler handler: toRemoveHandlers) {
			logger.removeHandler(handler);
		}
		//logger.addHandler(fileTxt);

		handlers = logger.getHandlers();
		System.out.println("Handlers are:");
		for (Handler handler: handlers) {
			System.out.println(handler);
		}
		*/

		File usersFile = new File(System.getenv("FABAN_HOME")+"/users.list");
		BufferedReader bw = new BufferedReader(new FileReader(usersFile));
		String line;
		while ((line = bw.readLine()) != null) {
			String tokens[] = line.split(" ");
			UserPasswordPair pair = new UserPasswordPair(tokens[0], tokens[1], tokens[2]);
			userPasswordList.add(pair);
		}

		bw.close();

		thisUserPasswordPair = userPasswordList.get(context.getThreadId());
		System.out.println("Thread: " + context.getThreadId() + " uses user-pass for GUID: " + thisUserPasswordPair.getGuid());
		elggMetrics = new ElggDriverMetrics();
		context.attachMetrics(elggMetrics);

		hostUrl = "http://"+context.getXPathValue("/webbenchmark/serverConfig/host")+":"+context.getXPathValue("/webbenchmark/serverConfig/port");
		//hostUrl = "http://spaten";
		random = new Random();


		if (semaphore == null) {
			String period = System.getenv("SERIALIZING");

			if (period == null)
				semaphore = new Semaphore(1 << 31);
			else {
				semaphore = new Semaphore(0, true);
				timer     = new Timer();

				timer.schedule(new TimerTask() {
					@Override
					public void run() {
						if (semaphore.hasQueuedThreads())
							semaphore.release();
					}
				}, 0, Integer.parseInt(period));
			}
		}
	}

	private String getRandomUserGUID() {
		int randomIndex = random.nextInt(userPasswordList.size());
		String randomGuid = userPasswordList.get(randomIndex).getGuid();
		while (randomGuid == thisClient.getGuid()) {
			randomIndex = random.nextInt(userPasswordList.size());
			randomGuid = userPasswordList.get(randomIndex).getGuid();
		}
		return randomGuid;
	}

	private UserPasswordPair getRandomUser() {
		int randomIndex = random.nextInt(userPasswordList.size());
		return userPasswordList.get(randomIndex);
	}

	Pattern pattern1 = Pattern.compile("input type=\"hidden\" name=\"__elgg_token\" value=\"(.*?)\"");
	Pattern pattern2 = Pattern.compile("input type=\"hidden\" name=\"__elgg_ts\" value=\"(.*?)\"");

	private void updateElggTokenAndTs(Web20Client client, StringBuilder sb, boolean updateGUID) {
		// The code for obtaining the token and ts is changed by a_ansaarii, the old code does not work
		// this code is derived from UserGenerator.java
		int elggTokenStartIndex = sb.indexOf("\"__elgg_token\":\"") + "\"__elgg_token\":\"".length();
        	int elggTokenEndIndex = sb.indexOf("\"", elggTokenStartIndex);
        	String elggToken = sb.substring(elggTokenStartIndex, elggTokenEndIndex);
        	//System.out.println("Elgg Token = "+elggToken);

       	int elggTsStartIndex = sb.indexOf("\"__elgg_ts\":") + "\"__elgg_ts\":".length();
        	int elggTsEndIndex = sb.indexOf(",", elggTsStartIndex);
        	String elggTs = sb.substring(elggTsStartIndex, elggTsEndIndex);
        	//System.out.println("Elgg Ts = "+elggTs);

        	client.setElggToken(elggToken);
        	client.setElggTs(elggTs);

		// These lines are commented by a_ansaarii, they are the implementations that are not working with elgg 3.3.20
		//String elggToken = null;
		//String elggTs = null;

	    	//Matcher matcherToken = pattern1.matcher(sb.toString());
	    	//while (matcherToken.find()) {
	    	//	elggToken = matcherToken.group(1);
	    	//}

		//Matcher matcherTs = pattern2.matcher(sb.toString());
		//while (matcherTs.find()) {
		//	elggTs = matcherTs.group(1);
		//}

		//if (null != elggToken) {
		//	client.setElggToken(elggToken);
		//}

		//if (null != elggTs) {
		//	client.setElggTs(elggTs);
		//}

		if (updateGUID) {
			// Get the Json
			int startIndex = sb.indexOf("var elgg = ");
			int endIndex = sb.indexOf(";", startIndex);
			String elggJson = sb.substring(startIndex + "var elgg = ".length(),
					endIndex);

			JSONObject elgg = new JSONObject(elggJson);
			if (!elgg.getJSONObject("session").isNull("user")) {
				JSONObject userSession = elgg.getJSONObject("session")
						.getJSONObject("user");
				Integer elggGuid = userSession.getInt("guid");
					client.setGuid(elggGuid.toString());
			}
		}

		logger.finer("Elgg Token = "+elggToken+" Elgg Ts = "+elggTs);
	}

	private void updateNumActivities(Web20Client client, StringBuilder sb) {
		//var numactivities = 582;
		int startIndex = sb.indexOf("var numactivities = ")+"var numactivities = ".length();
		int endIndex = sb.indexOf(";", startIndex);
		client.setNumActivities(sb.substring(startIndex, endIndex));

	}

	@BenchmarkOperation(name = "BrowsetoElgg",
						//max90th = 3.0,
						percentileLimits= {500,1000,2000},
						timing = Timing.MANUAL)
	/**
	 * A new client accesses the home page. The "new client" is selected from a list maintained of possible users and their passwords.
	 * The details of the new client are stored in the
	 * @throws Exception
	 */
	public void browseToElgg() throws Exception {
		boolean success = false;
		if (!inited)
			logger.info("Inited thread" + context.getThreadId());
		inited = true;
		logger.fine(context.getThreadId() +" : Doing operation: browsetoelgg");

		if (thisClient.getClientState() == ClientState.LOGGED_OUT) {

			thisClient.setGuid(thisUserPasswordPair.getGuid());
			thisClient.setUsername(thisUserPasswordPair.getUserName());
			thisClient.setPassword(thisUserPasswordPair.getPassword());
			thisClient.setLoggedIn(false);
			System.out.println("Logging in: "+thisClient.getUsername());
			/*
			thisClient.setGuid("43");
			thisClient.setUsername("tpalit");
			thisClient.setPassword("password1234");
			*/

			HttpTransport http = HttpTransport.newInstance();
			http.addTextType("application/xhtml+xml");
			http.addTextType("application/xml");
			http.addTextType("q=0.9,*/*");
			http.addTextType("q=0.8");
			http.setFollowRedirects(true);

			thisClient.setHttp(http);

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = http.fetchURL(hostUrl + ROOT_URL);
			context.recordTime();

			updateElggTokenAndTs(thisClient, sb, false);
			updateNumActivities(thisClient, sb);
			printErrorMessageIfAny(sb, null);

			// commented by a_ansaarii, these urls do not exist on elgg 3.3.20
			/*for (String url : ROOT_URLS) {
				System.out.println("url is: " + hostUrl + url);
				http.readURL(hostUrl + url);
			}*/
		}
		else {
			context.recordTime();
			context.recordTime();
		}

		elggMetrics.attemptHomePageCnt++;

	}

@BenchmarkOperation(name = "AccessHomepage",
						//max90th = 3.0,
						percentileLimits= {500,1000,2000},
						timing = Timing.MANUAL)
	/**
	 * A logged in client accesses the home page
	 * @throws Exception
	 */
	public void accessHomePage() throws Exception {
		boolean success = false;
		logger.fine(context.getThreadId()
				+ " : Doing operation: accessHomePage");

		/*
		 * thisClient.setGuid("43"); thisClient.setUsername("tpalit");
		 * thisClient.setPassword("password1234");
		 */


//		thisClient.setClientState(ClientState.AT_HOME_PAGE);
		semaphore.acquire();
		context.recordTime();
		StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + ROOT_URL);
		context.recordTime();

		updateElggTokenAndTs(thisClient, sb, false);
		updateNumActivities(thisClient, sb);

		printErrorMessageIfAny(sb, null);

		// commented by a_ansaarii, these urls do not exist on elgg 3.3.20
		/*for (String url : ROOT_URLS) {
			thisClient.getHttp().readURL(hostUrl + url);
		}*/

		elggMetrics.attemptHomePageCnt++;

	}

	@BenchmarkOperation(name = "DoLogin",
						//max90th = 3.0,
						percentileLimits= {500,1000,2000},
						timing = Timing.MANUAL)
	public void doLogin() throws Exception {
		boolean success = false;
		long loginStart = 0, loginEnd = 0;
		logger.fine(context.getThreadId() + " : Doing operation: doLogin with"
				+ thisClient.getUsername());

		/*
		 * To do the login, To login, we need four parameters in the POST query
		 * 1. Elgg token 2. Elgg timestamp 3. user name 4. password
		 */
		String postRequest = "__elgg_token=" + thisClient.getElggToken()
				+ "&__elgg_ts=" + thisClient.getElggTs() + "&username="
				+ thisClient.getUsername() + "&password="
				+ thisClient.getPassword();

		// commented by a_ansaarii, these urls do not exist on elgg 3.3.20
		/*for (String url : LOGIN_URLS) {
			thisClient.getHttp().readURL(hostUrl + url);
		}*/

		Map<String, String> headers = new HashMap<String, String>();
		headers.put("Accept",
				"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
		headers.put("Accept-Language", "en-US,en;q=0.5");
		headers.put("Accept-Encoding", "gzip, deflate");
		headers.put("Referer", hostUrl + "/");
		headers.put("User-Agent",
				"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
		headers.put("Content-Type", "application/x-www-form-urlencoded");

		semaphore.acquire();
		context.recordTime();
		StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + LOGIN_URL,
				postRequest, headers);
		context.recordTime();

		updateElggTokenAndTs(thisClient, sb, true);
		printErrorMessageIfAny(sb, postRequest);


		if (sb.toString().contains("You have been logged in")) {
			logger.fine("Successfully logged in: "+thisClient.getUsername());
			System.out.print("PRINT: Successfully logged in\n");
		} else {
			logger.fine("!!!!!!!!!!!!!!!!!! Failed to log in :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
			System.out.print("PRINT: Failed to login!\n");
			throw new RuntimeException(sb.toString());
		}
		thisClient.setLoggedIn(true);
		thisClient.setClientState(ClientState.LOGGED_IN);
		success = true;

		if (success)
			elggMetrics.attemptLoginCnt++;
	}

	@BenchmarkOperation(name = "UpdateActivity",// max90th = 1.0,
                        //percentileLimits= {1.0},
						percentileLimits= {500,750,1000},
                        timing = Timing.MANUAL)
	public void updateActivity() throws Exception {

		boolean success = false;

		if (thisClient.getClientState() == ClientState.LOGGED_IN) {
			logger.fine(context.getThreadId() +" : Doing operation: updateActivity");

			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			headers.put("Accept-Language", "en-US,en;q=0.5");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/activity");
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
			headers.put("Content-Type", "application/x-www-form-urlencoded");


			String postString = "options%5Bcount%5D=false&options%5Bpagination%5D=false&options%5Boffset%5D=0&options%5Blimit%5D=5&count="+thisClient.getNumActivities();
			// Note: the %5B %5D are [ and ] respectively.
			// #TODO: Fix the count value.
			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(
					hostUrl + RIVER_UPDATE_URL, postString, headers);
			context.recordTime();

			if (sb.toString().contains("Sorry, you cannot perform this action while logged out.")) {
				logger.fine("startNewChat: !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!User logged out!!");
			}
			printErrorMessageIfAny(sb, postString);

			success = true;
		}
		else {
			context.recordTime();
			context.recordTime();
		}

		if (success) {
			elggMetrics.attemptUpdateActivityCnt++;
		}
	}

	/**
	 * Add friend
	 *
	 * @throws Exception
	 */
	@BenchmarkOperation(name = "AddFriend",
						//max90th = 3.0,
						percentileLimits= {500,750,1000},
						timing = Timing.MANUAL)
	public void addFriend() throws Exception {
		boolean success = false;
		StringBuilder sb = null;
		if (thisClient.getClientState() == ClientState.LOGGED_IN) {
			logger.fine(context.getThreadId() +" : Doing operation: addFriend");

			UserPasswordPair user = getRandomUser();
			String friendeeGuid = user.getGuid();
			System.out.println("User: " + thisClient.getGuid() + " becomes friend of " + friendeeGuid);
			String queryString = "friend=" + friendeeGuid + "&__elgg_ts="
					+ thisClient.getElggTs() + "&__elgg_token="
					+ thisClient.getElggToken();
			String postString = "__elgg_ts="
					+ thisClient.getElggTs() + "&__elgg_token="
					+ thisClient.getElggToken();

			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			headers.put("Accept-Language", "en-US,en;q=0.5");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/profile/"+user.getUserName());
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
			headers.put("Content-Type", "application/x-www-form-urlencoded");
			headers.put("X-Requested-With", "XMLHttpRequest");

			semaphore.acquire();
			context.recordTime();
			sb = thisClient.getHttp().fetchURL(
					hostUrl + DO_ADD_FRIEND + "?" + queryString, postString, headers);
			context.recordTime();

			if (sb.toString().contains("Sorry, you cannot perform this action while logged out.")) {
				logger.fine("startNewChat:!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!User logged out!!");
			}
			printErrorMessageIfAny(sb, postString);
			success = true;
		}
		else {
			context.recordTime();
			context.recordTime();
		}

		if (success) {
			elggMetrics.attemptAddFriendsCnt++;
		}/* else {
			if (thisClient.getClientState() == ClientState.AT_HOME_PAGE) {
				doLogin();
			} else if (thisClient.getClientState() == ClientState.LOGGED_OUT) {
				accessHomePage();
				doLogin();
			}
		}*/

	}

	/**
	 * Receive a chat message.
	 */
	@BenchmarkOperation(name = "ReceiveChatMessage",
			//max90th = 1.0,
            //percentileLimits= {1.0},
			percentileLimits= {500,750,1000},
			timing = Timing.MANUAL)
	public void receiveChatMessage() throws Exception {
		boolean success = false;
		StringBuilder sb = null;
		if (thisClient.getClientState() == ClientState.LOGGED_IN) {
			logger.fine(context.getThreadId() +" : Doing operation: receiveChatMessage");

			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			headers.put("Accept-Language", "en-US,en;q=0.5");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/activity");
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");


			semaphore.acquire();
			context.recordTime();
			sb = thisClient.getHttp().fetchURL(hostUrl+CHAT_RECV_URL+"?__elgg_ts="+thisClient.getElggTs()+"&__elgg_token="+thisClient.getElggToken(), headers);
			context.recordTime();

			if (sb.toString().contains("Sorry, you cannot perform this action while logged out.")) {
				logger.fine("receiveNewChat: !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!User logged out!!");
			}
			printErrorMessageIfAny(sb, null);
			success = true;
		}
		else {
			context.recordTime();
			context.recordTime();
		}

		if (success) {
			elggMetrics.attemptRecvChatMessageCnt ++;
		}
	}

	/**
	 * Send a chat message
	 *
	 * @throws Exception
	 */
	@BenchmarkOperation(name = "SendChatMessage",
						//max90th = 1.0,
			            percentileLimits= {500,750,1000},
						timing = Timing.MANUAL)
	public void sendChatMessage() throws Exception {
		boolean success = false;
		StringBuilder sb = null;
		if (thisClient.getClientState() == ClientState.LOGGED_IN) {
			logger.fine(context.getThreadId() +" : Doing operation: sendChatMessage");

			if (thisClient.getChatSessionList().isEmpty()) {
				startNewChat();
			} else {
				// Continue an existing chat conversation
				logger.fine(context.getThreadId() +" : Doing suboperation: continue existing conversation");
				String chatGuid = thisClient.getChatSessionList().get(random.nextInt(thisClient.getChatSessionList().size()));

					Map<String, String> headers = new HashMap<String, String>();
					headers.put("Accept",
							"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
					headers.put("Accept-Language", "en-US,en;q=0.5");
					headers.put("Accept-Encoding", "gzip, deflate");
					headers.put("Referer", hostUrl + "/activity");
					headers.put("User-Agent",
							"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
					headers.put("Content-Type", "application/x-www-form-urlencoded");


					String postString = "chatsession="+chatGuid+"&chatmessage="
							+RandomStringGenerator.generateRandomString(15, Mode.ALPHA);

					semaphore.acquire();
					context.recordTime();
					sb = thisClient.getHttp().fetchURL(hostUrl+CHAT_POST_URL
														+"?__elgg_token="+thisClient.getElggToken()
														+"&__elgg_ts="+thisClient.getElggTs()
													, postString, headers);
					context.recordTime();

					if (sb.toString().contains("Sorry, you cannot perform this action while logged out.")) {
						logger.fine("continueChat: !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!User logged out!!");
					}
					printErrorMessageIfAny(sb, postString);

			}
			success = true;
		}
		else {
			context.recordTime();
			context.recordTime();
		}

		if (success) {
				elggMetrics.attemptSendChatMessageCnt ++;
		} /*else {
			if (thisClient.getClientState() == ClientState.AT_HOME_PAGE) {
				doLogin();
			} else if (thisClient.getClientState() == ClientState.LOGGED_OUT) {
				accessHomePage();
				doLogin();
			}
		}*/
	}

	private void startNewChat() throws Exception {
		StringBuilder sb = null;
		String postString = null;
		String chatGuid = null;

		logger.fine(context.getThreadId() +" : Doing suboperation: start new conversation");
		// Create a new chat communication between two logged in users
		String chateeGuid = getRandomUserGUID();

		Map<String, String> headers = new HashMap<String, String>();
		headers.put("Accept",
				"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
		headers.put("Accept-Language", "en-US,en;q=0.5");
		headers.put("Accept-Encoding", "gzip, deflate");
		headers.put("Referer", hostUrl + "/activity");
		headers.put("User-Agent",
				"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
		headers.put("Content-Type", "application/x-www-form-urlencoded");


		postString = "invite=" + chateeGuid + "&__elgg_ts="
				+ thisClient.getElggTs() + "&__elgg_token="
				+ thisClient.getElggToken();

		semaphore.acquire();
		context.recordTime();
		sb = thisClient.getHttp().fetchURL(hostUrl + CHAT_CREATE_URL,
				postString, headers);
		context.recordTime();

		assert (thisClient.getHttp().getResponseCode() == 200);

		if (sb.toString().contains("Sorry, you cannot perform this action while logged out.")) {
			logger.fine("startNewChat: create session: !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!User logged out!!");
		}

		chatGuid = sb.toString();

		thisClient.getChatSessionList().add(chatGuid);

		/*
		headers.put("Referer", hostUrl + "/activity");

		// Send a message
		postString = "chatsession=" + chatGuid + "&chatmessage="
				+ RandomStringGenerator.generateRandomString(15, Mode.ALPHA);
		sb = thisClient.getHttp().fetchURL(
				hostUrl + CHAT_POST_URL + "?__elgg_token="
						+ thisClient.getElggToken() + "&__elgg_ts="
						+ thisClient.getElggTs(), postString, headers);
		if (sb.toString().contains("Sorry, you cannot perform this action while logged out.")) {
			logger.fine("startNewChat: send Message: !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!User logged out!!");
		}
		printErrorMessageIfAny(sb, postString);
		assert (thisClient.getHttp().getResponseCode() == 200);
	*/
	}

	/**
	 * Post something on the Wall (actually on the Wire but from the Wall!).
	 *
	 * @throws Exception
	 */
	@BenchmarkOperation(name = "PostSelfWall",
						//max90th = 1.0,
			            percentileLimits= {500,750,1000},
						timing = Timing.MANUAL)
	public void postSelfWall() throws Exception {
		boolean success = false;

		if (thisClient.getClientState() == ClientState.LOGGED_IN) {
			logger.fine(context.getThreadId()+context.getThreadId() +" : Doing operation: post wall");

			String status = "Hello world! "
					+ new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.SSS")
							.format(new Date());
			String postRequest = "__elgg_token=" + thisClient.getElggToken()
					+ "&__elgg_ts=" + thisClient.getElggTs() + "&status=" + status
					+ "&address=&access_id=-2&origin=wall&container_guid="
					+ thisClient.getGuid()+"&X-Requested-With=XMLHttpRequest&river=true&widget=0";
			//&X-Requested-With=XMLHttpRequest&container_guid=43&river=true&widget=0
			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			headers.put("Accept-Language", "en-US,en;q=0.5");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/activity");
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
			headers.put("Content-Type", "application/x-www-form-urlencoded");

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + WALL_URL,
					postRequest, headers);
			context.recordTime();

			printErrorMessageIfAny(sb, postRequest);
			updateElggTokenAndTs(thisClient, sb, false);
			success = true;
		}
		else {
			context.recordTime();
			context.recordTime();
		}

		if (success) {
			elggMetrics.attemptPostWallCnt++;
		} /*else {
			if (thisClient.getClientState() == ClientState.AT_HOME_PAGE) {
				doLogin();
			} else if (thisClient.getClientState() == ClientState.LOGGED_OUT) {
				accessHomePage();
				doLogin();
			}
		}	*/

	}

	/**
	 * Post something on the Wall (actually on the Wire but from the Wall!).
	 *
	 * @throws Exception
	 */
	@BenchmarkOperation(name = "Logout",
						//max90th = 3.0,
			            percentileLimits= {500,750,1000},
						timing = Timing.MANUAL)
	public void logout() throws Exception {
		boolean success = false;

		if (thisClient.getClientState() == ClientState.LOGGED_IN) {
			logger.fine(context.getThreadId() +" : Doing operation: logout");

			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			headers.put("Accept-Language", "en-US,en;q=0.5");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/activity");
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + LOGOUT_URL
					+"?__elgg_ts="+thisClient.getElggTs()+"&__elgg_token="+thisClient.getElggToken(), headers);
			context.recordTime();

			printErrorMessageIfAny(sb, null);
			//System.out.println(sb);
			updateElggTokenAndTs(thisClient, sb, false);
			thisClient.setClientState(ClientState.LOGGED_OUT);
			thisClient.setLoggedIn(false);
			success = true;
		}
		else {
			context.recordTime();
			context.recordTime();
		}

		if (success) {
			elggMetrics.attemptLogoutCnt++;
		} /*else {
			if (thisClient.getClientState() == ClientState.AT_HOME_PAGE) {
				doLogin();
			} else if (thisClient.getClientState() == ClientState.LOGGED_OUT) {
				accessHomePage();
				doLogin();
			}
		}*/

	}

	/**
	 *
	 * Register a new user.
	 *
	 */
	@BenchmarkOperation(name = "Register",
						//max90th = 3.0,
						percentileLimits= {500,1000,2000},
						timing = Timing.MANUAL)
	public void register() throws Exception {
		boolean success = false;

		Web20Client tempClient = new Web20Client();
		HttpTransport http;

		if (thisClient.getClientState() == ClientState.LOGGED_OUT) {
			http = HttpTransport.newInstance();
			tempClient.setHttp(http);

			logger.fine(context.getThreadId() +" : Doing operation: register");

			// Navigate to the home page
			semaphore.acquire();
			StringBuilder sb = tempClient.getHttp().fetchURL(hostUrl + ROOT_URL);

			updateElggTokenAndTs(tempClient, sb, false);
			// commented by a_ansaarii
			/*for (String url : ROOT_URLS) {
				tempClient.getHttp().readURL(hostUrl + url);
				// System.out.println(sb.indexOf("__elgg_token"));
			}*/


			// Click on Register link and generate user name and password
            semaphore.acquire();
			tempClient.getHttp().fetchURL(hostUrl + REGISTER_PAGE_URL);
			String userName = RandomStringGenerator.generateRandomString(10,
					RandomStringGenerator.Mode.ALPHA);
			String password = RandomStringGenerator.generateRandomString(10,
					RandomStringGenerator.Mode.ALPHA);
			String email = RandomStringGenerator.generateRandomString(7,
					RandomStringGenerator.Mode.ALPHA)
					+ "@"
					+ RandomStringGenerator.generateRandomString(5,
							RandomStringGenerator.Mode.ALPHA) + ".co.in";
			tempClient.setUsername(userName);
			tempClient.setPassword(password);
			tempClient.setEmail(email);

			String postString = "__elgg_token=" + tempClient.getElggToken()
					+ "&__elgg_ts=" + tempClient.getElggTs() + "&name="
					+ tempClient.getUsername() + "&email=" + tempClient.getEmail()
					+ "&username=" + tempClient.getUsername() + "&password="
					+ tempClient.getPassword() + "&password2=" + tempClient.getPassword()
					+ "&friend_guid=0+&invitecode=&submit=Register";
			// __elgg_token=0c3a778d2b74a7e7faf63a6ba55d4832&__elgg_ts=1434992983&name=display_name&email=tapti.palit%40gmail.com&username=user_name&password=pass_word&password2=pass_word&friend_guid=0&invitecode=&submit=Register

			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			headers.put("Accept-Language", "en-US,en;q=0.5");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/register");
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
			headers.put("Content-Type", "application/x-www-form-urlencoded");

	        semaphore.acquire();
			context.recordTime();
			sb = tempClient.getHttp().fetchURL(hostUrl + DO_REGISTER_URL, postString,
					headers);
			context.recordTime();

			printErrorMessageIfAny(sb, postString);
			// System.out.println(sb);

			// added by a_ansaarii: we should set the flag somewhere, right?
			success = true;
		}
		else {
			context.recordTime();
			context.recordTime();
		}

		if (success)
			elggMetrics.attemptRegisterCnt++;

	}

	private void printErrorMessageIfAny(StringBuilder sb, String postRequest) {
		String htmlContent = sb.toString();
		//tem.out.println(htmlContent);
		String startTag = "<li class=\"elgg-message elgg-state-error\">";
		String endTag = "</li>";
		if (htmlContent.contains("elgg-system-messages")) {
			if (htmlContent.contains(startTag)) {
				int fromIndex = htmlContent.indexOf(startTag)+startTag.length();
				int toIndex = htmlContent.indexOf(endTag, fromIndex);
				String error = htmlContent.substring(fromIndex, toIndex);
				if (!error.trim().isEmpty()) {
					logger.info("Thread id: "+context.getThreadId()+" User: "+thisClient.getUsername()+" logged in status: "+thisClient.isLoggedIn()+"\nError: "+error+"Post request was: "+postRequest);
					throw new RuntimeException("Error happened");
				}
			}
		}
	}

	@OnceAfter
	public void cleanUp() throws IOException {
		Map<String, String> headers = new HashMap<String, String>();
		headers.put("Accept",
				"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
		headers.put("Accept-Language", "en-US,en;q=0.5");
		headers.put("Accept-Encoding", "gzip, deflate");
		headers.put("Referer", hostUrl + "/activity");
		headers.put("User-Agent",
				"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
		headers.put("Content-Type", "application/x-www-form-urlencoded");
		headers.put("X-Requested-With", "XMLHttpRequest");

		// Go over all chat sessions and leave them.
		logger.fine("Cleaning up chat sessions");
		for (String chatGuid: thisClient.getChatSessionList()) {
			String postString = "chatsession="+chatGuid+"&__elgg_ts="+thisClient.getElggTs()+"&__elgg_token="+thisClient.getElggToken();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + LEAVE_CHAT_URL, postString, headers);
			System.out.println(sb.toString());
		}
	}
	static class ElggDriverMetrics implements CustomMetrics {

		int attemptLoginCnt = 0;
		int attemptHomePageCnt = 0;
		int attemptPostWallCnt = 0;
		int attemptUpdateActivityCnt = 0;
		int attemptAddFriendsCnt = 0;
		int attemptSendChatMessageCnt = 0;
		int attemptRecvChatMessageCnt = 0;
		int attemptLogoutCnt = 0;
		int attemptRegisterCnt = 0;

		@Override
		public void add(CustomMetrics arg0) {
			ElggDriverMetrics e = (ElggDriverMetrics) arg0;
			this.attemptHomePageCnt += e.attemptHomePageCnt;
			this.attemptLoginCnt += e.attemptLoginCnt;
			this.attemptPostWallCnt += e.attemptPostWallCnt;
			this.attemptUpdateActivityCnt += e.attemptUpdateActivityCnt;
			this.attemptAddFriendsCnt += e.attemptAddFriendsCnt;
			this.attemptSendChatMessageCnt += e.attemptSendChatMessageCnt;
			this.attemptRecvChatMessageCnt += e.attemptRecvChatMessageCnt;
			this.attemptLogoutCnt += e.attemptLogoutCnt;
			this.attemptRegisterCnt += e.attemptRegisterCnt;
		}

		@Override
		public Element[] getResults() {
			Element[] el = new Element[9];
			el[0] = new Element();
			el[0].description = "Number of times home page was actually attempted to be accessed.";
			el[0].passed = true;
			el[0].result = "" + this.attemptHomePageCnt;
			el[1] = new Element();
			el[1].description = "Number of times login was actually attempted.";
			el[1].passed = true;
			el[1].result = "" + this.attemptLoginCnt;
			el[2] = new Element();
			el[2].description = "Number of times posting on wall was actually attempted.";
			el[2].passed = true;
			el[2].result = "" + this.attemptPostWallCnt;
			el[3] = new Element();
			el[3].description = "Number of times update activity was actually attempted.";
			el[3].passed = true;
			el[3].result = "" + this.attemptUpdateActivityCnt;
			el[4] = new Element();
			el[4].description = "Number of times add friends was actually attempted.";
			el[4].passed = true;
			el[4].result = "" + this.attemptAddFriendsCnt;
			el[5] = new Element();
			el[5].description = "Number of times send message was actually attempted.";
			el[5].passed = true;
			el[5].result = "" + this.attemptSendChatMessageCnt;
			el[6] = new Element();
			el[6].description = "Number of times receive message was actually attempted.";
			el[6].passed = true;
			el[6].result = "" + this.attemptRecvChatMessageCnt;
			el[7] = new Element();
			el[7].description = "Number of times logout was actually attempted.";
			el[7].passed = true;
			el[7].result = "" + this.attemptLogoutCnt;
			el[8] = new Element();
			el[8].description = "Number of times register was actually attempted.";
			el[8].passed = true;
			el[8].result = "" + this.attemptRegisterCnt;
			return el;
		}

		public Object clone() {
			ElggDriverMetrics clone = new ElggDriverMetrics();
			clone.attemptHomePageCnt = this.attemptHomePageCnt;
			clone.attemptLoginCnt = this.attemptLoginCnt;
			clone.attemptPostWallCnt = this.attemptPostWallCnt;
			clone.attemptUpdateActivityCnt = this.attemptUpdateActivityCnt;
			clone.attemptAddFriendsCnt = this.attemptAddFriendsCnt;
			clone.attemptSendChatMessageCnt = this.attemptSendChatMessageCnt;
			clone.attemptRecvChatMessageCnt = this.attemptRecvChatMessageCnt;
			clone.attemptLogoutCnt = this.attemptLogoutCnt;
			clone.attemptRegisterCnt = this.attemptRegisterCnt;
			return clone;
		}
	}

	public static void main(String[] pp) throws Exception {
		Web20Driver driver = new Web20Driver();
		for (int i= 0; i<1; i++) {
			//System.out.println("Initing RUN..."+i);
			driver.browseToElgg();
			//System.out.println("Doing login ......................................");
			long start = System.currentTimeMillis();
			driver.doLogin();
			long end = System.currentTimeMillis();
			System.out.println("RUN\t"+i+"\t"+(end-start));
			System.out.println("Doing add friend ....................................");
			driver.addFriend();
			System.out.println("Doing post wall ............................");
			driver.postSelfWall();

			System.out.println("Doing send chat .................................");
			driver.sendChatMessage();
			System.out.println("Doing recv chat ......................................");
			driver.receiveChatMessage();
			System.out.println("Cleaning up chat ..................................");
			driver.cleanUp();
			System.out.println("Doing logout ................................");
			driver.logout();
			System.out.println("Doing register ...........................");
			driver.register();

		}
	}



}

