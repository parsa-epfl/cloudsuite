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
import java.util.Scanner;

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
    cycleTime      = 200,
    cycleType      = CycleType.THINKTIME,
    cycleDeviation = 2
)

@MatrixMix(
    operations = {"BrowsetoElgg", "DoLogin", "AddFriend", "Register", "Logout", "CheckActivity", "Dashboard", "AccessHomePage", "RemoveFriend", "GetNotifications", "Inbox", "CheckProfile", "CheckFriends", "CheckWire", "PostWire", "SendMessage", "ReadMessage", "CheckBlog", "SentMessages", "PostBlog", "DeleteMessage", "Like", "ReplyWire", "Comment", "Search"},
    mix        = {@Row({0, 100, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({100, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0}), @Row({70, 0, 0, 30, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 2, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 3, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 3, 5, 5, 5, 5, 5, 5, 5, 5, 5, 2, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 3, 5, 5, 5, 2, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2}), @Row({0, 0, 5, 0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 0, 5, 3, 2})}
)


/**
 * The main driver class.
 *
 * Operations :-
 *
 * Browse the home page
 * Login existing user (X)
 * Send friend request
 * Register a new user
 * Logout a logged in user
 * Check recent activites of the site
 * Remove a friend
 * Check the notifications
 * Check the inbox
 * Check a user's profile
 * Browse the friends list
 * Browse the wires page
 * Post a wire
 * Send a message
 * Read a message
 * Browse the blogs page
 * Browse the send messages page
 * Post a blog
 * Delete a message
 * Like a post or wire
 * Reply to a wire
 * Comment on a blog
 * Search
 *
 * @author Tapti Palit
 * @author Ali Ansari
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

	private final String LOGIN_URL = "/action/login";

	private final String[] ACTIVITY_URLS = new String[] {"/activity", "/activity/owner/", "/activity/friends/"};

	private final String DASHBOARD_URL = "/dashboard";

	private final String REGISTER_PAGE_URL = "/register";

	private final String DO_REGISTER_URL = "/action/register";
	private final String DO_ADD_FRIEND = "/action/friends/add";
	private final String DO_REMOVE_FRIEND = "/action/friends/remove";

	private final String LOGOUT_URL = "/action/logout";
	private final String NOTIFICATIONS_URL = "/site_notifications/owner/";
	private final String INBOX_URL = "/messages/inbox/";
	private final String PROFILE_URL = "/profile/";
	private final String FRIENDS_URL = "/friends/";
	private final String[] WIRE_URLS = new String[] {"/thewire", "/thewire/owner/", "/thewire/friends/"};
	private final String POST_WIRE_URL = "/action/thewire/add";
	private final String SEND_MESSAGE_URL = "/action/messages/send";
	private final String READ_MESSAGE_URL = "/messages/read/";
	private final String[] BLOG_URLS = new String[] {"/blog", "/blog/owner/", "/blog/friends/"};
	private final String SENT_MESSAGES_URL = "/messages/sent/";
	private final String POST_BLOG_URL = "/action/blog/save";
	private final String DELETE_MESSAGE_URL = "/action/messages/process/";
	private final String LIKE_URL = "/action/likes/add";
	private final String COMMENT_URL = "/action/comment/save";
	private final String SEARCH_URL = "/members/search";

	public Web20Driver() throws SecurityException, IOException, XPathExpressionException {

		thisClient = new Web20Client();
		thisClient.setClientState(ClientState.LOGGED_OUT);
		thisClient.setFriendsList(new ArrayList<String>());
		thisClient.setMessagesGuids(new ArrayList<String>());
		thisClient.setBlogsGuids(new ArrayList<String>());
		thisClient.setWiresGuids(new ArrayList<String>());
		thisClient.setLoggedIn(false);


		context = DriverContext.getContext();
		userPasswordList = new ArrayList<UserPasswordPair>();

		logger = context.getLogger();
		logger.setLevel(Level.INFO);

		File usersFile = new File(System.getenv("FABAN_HOME")+"/users.list");
		BufferedReader bw = new BufferedReader(new FileReader(usersFile));
		String line;
		while ((line = bw.readLine()) != null) {
			String tokens[] = line.split(" ");
			UserPasswordPair pair = new UserPasswordPair(tokens[0], tokens[1], tokens[2]);
			userPasswordList.add(pair);
		}

		bw.close();


		elggMetrics = new ElggDriverMetrics();
		context.attachMetrics(elggMetrics);

		hostUrl = context.getXPathValue("/webbenchmark/serverConfig/protocol")+"://"+context.getXPathValue("/webbenchmark/serverConfig/host")+":"+context.getXPathValue("/webbenchmark/serverConfig/port");
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

	@BenchmarkOperation(name = "Search",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client send a search request
		 * @throws Exception
		 */
		public void search() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: sending a search request "
					+ thisClient.getUsername());


			String username = getRandomUser().getUserName();

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + SEARCH_URL + "?member_query=" + username);
			context.recordTime();

			if (sb.toString().contains("Member search for")) {
				logger.fine("Successfully sent a search request for term "+username);
				System.out.print("PRINT: Successfully sent a search request\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to send a search request for term: "+username+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to send a search request!\n");
				throw new RuntimeException(sb.toString());
			}



			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After search: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptSearchCnt++;
		}



	@BenchmarkOperation(name = "SentMessages",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client reads a sent messages
		 * @throws Exception
		 */
		public void sentMessages() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: checking sent messages with"
					+ thisClient.getUsername());


			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + SENT_MESSAGES_URL + thisClient.getUsername());
			context.recordTime();

			if (sb.toString().contains("Sent messages</h2>")) {
				logger.fine("Successfully read the sent messages of: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully read the sent messages\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to read the sent messages of:"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to read the read the sent messages!\n");
				throw new RuntimeException(sb.toString());
			}



			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After read the sent messages: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptSentMessagesCnt++;
		}


	@BenchmarkOperation(name = "Comment",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client comments on a blog post
		 * @throws Exception
		 */
		public void comment() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: commenting on a post by "
					+ thisClient.getUsername());


			int blogs_size = thisClient.getBlogsGuidsSize();

			if( blogs_size == 0){
				context.recordTime();
				context.recordTime();
				elggMetrics.attemptCommentFailedCnt++;
				return;
			}

			String guid = "";
			int index = random.nextInt(blogs_size);
			guid = thisClient.getBlogsGuids(index);

			String postRequest = "__elgg_token=" + thisClient.getElggToken()
				+ "&__elgg_ts=" + thisClient.getElggTs()
				+ "&generic_comment=" + RandomStringGenerator.generateRandomString(15, Mode.ALPHA)
				+ "&entity_guid=" + guid;


			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + COMMENT_URL, postRequest);
			context.recordTime();

			System.out.println("PRINT: Successfully commented on a blog post");
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptCommentCnt++;
		}



	@BenchmarkOperation(name = "ReplyWire",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client replies to a wire
		 * @throws Exception
		 */
		public void replyWire() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: replying to a wire by "
					+ thisClient.getUsername());


			int wires_size = thisClient.getWiresGuidsSize();

			if( wires_size == 0){
				context.recordTime();
				context.recordTime();
				elggMetrics.attemptReplyWireFailedCnt++;
				return;
			}

			String guid = "";
			int index = random.nextInt(wires_size);
			guid = thisClient.getWiresGuids(index);

			String postRequest = "__elgg_token=" + thisClient.getElggToken()
				+ "&__elgg_ts=" + thisClient.getElggTs()
				+ "&body=" + RandomStringGenerator.generateRandomString(15, Mode.ALPHA)
				+ "&parent_guid=" + guid;


			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + POST_WIRE_URL, postRequest);
			context.recordTime();

			System.out.println("PRINT: Successfully replied to a wire post");
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptReplyWireCnt++;
		}


	@BenchmarkOperation(name = "Like",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client likes a blog or wire
		 * @throws Exception
		 */
		public void doLike() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: liking a blog or wire post by "
					+ thisClient.getUsername());


			int blogs_size = thisClient.getBlogsGuidsSize();
			int wires_size = thisClient.getWiresGuidsSize();

			if( blogs_size == 0 && wires_size == 0){
				context.recordTime();
				context.recordTime();
				elggMetrics.attemptLikeFailedCnt++;
				return;
			}

			int randomIndex = random.nextInt(2);
			String guid = "";
			if( (randomIndex == 0 && blogs_size != 0) || (randomIndex == 1 && wires_size == 0)){
				int index = random.nextInt(blogs_size);
				guid = thisClient.getBlogsGuids(index);
			}
			else{
				int index = random.nextInt(wires_size);
				guid = thisClient.getWiresGuids(index);
			}

			String postRequest = "__elgg_token=" + thisClient.getElggToken()
				+ "&__elgg_ts=" + thisClient.getElggTs()
				+ "&guid=" + guid;


			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + LIKE_URL, postRequest);
			context.recordTime();

			printErrorMessageIfAny(sb, null);

			System.out.println("PRINT: Successfully liked a blog or wire post");
			success = true;

			if (success)
				elggMetrics.attemptLikeCnt++;
		}




	@BenchmarkOperation(name = "DeleteMessage",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client deletes a received message
		 * @throws Exception
		 */
		public void deleteMessage() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: deleting a message of "
					+ thisClient.getUsername());


			int messages_size = thisClient.getMessagesGuidsSize();
			String message_guid = "";
			if(messages_size != 0){
				int randomIndex = random.nextInt(messages_size);
				message_guid = thisClient.getMessagesGuids(randomIndex);
				thisClient.removeMessagesGuids(message_guid);
				System.out.println("deleting message: "+message_guid+" from user: "+thisClient.getUsername()+" GUID: "+thisClient.getGuid());
			}
			else{
				context.recordTime();
				context.recordTime();
				elggMetrics.attemptDeleteMessageFailedCnt++;
				return;
			}

			String postRequest = "__elgg_token=" + thisClient.getElggToken()
				+ "&__elgg_ts=" + thisClient.getElggTs()
				+ "&message_id[]=" + message_guid
				+ "&delete=Delete";


			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + DELETE_MESSAGE_URL, postRequest);
			context.recordTime();

			printErrorMessageIfAny(sb, null);

			System.out.println("PRINT: Successfully deleted a message");
			success = true;

			if (success)
				elggMetrics.attemptDeleteMessageCnt++;
		}




	@BenchmarkOperation(name = "ReadMessage",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client reads a received message
		 * @throws Exception
		 */
		public void readMessage() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: checking others friends' list with"
					+ thisClient.getUsername());


			int messages_size = thisClient.getMessagesGuidsSize();
			String message_guid = "";
			if(messages_size != 0){
				int randomIndex = random.nextInt(messages_size);
				message_guid = thisClient.getMessagesGuids(randomIndex);
			}
			else{
				context.recordTime();
				context.recordTime();
				elggMetrics.attemptReadMessageFailedCnt++;
				return;
			}


			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + READ_MESSAGE_URL + message_guid);
			context.recordTime();

			if (sb.toString().contains("Reply</span>")) {
				logger.fine("Successfully read the message of: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully read the message\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to read the message of:"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to read the message!\n");
				throw new RuntimeException(sb.toString());
			}


			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After read the message: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptReadMessageCnt++;
		}


	@BenchmarkOperation(name = "CheckFriends",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client checks the friends' list
		 * @throws Exception
		 */
		public void checkFriends() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: checking others friends' list with"
					+ thisClient.getUsername());


			UserPasswordPair user = getRandomUser();

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + FRIENDS_URL + user.getUserName());
			context.recordTime();

			if (sb.toString().contains(user.getUserName()+"'s friends</h2>")) {
				logger.fine("Successfully checked the friends of: "+user.getUserName());
				System.out.print("PRINT: Successfully checked the friends\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to check friends of:"+user.getUserName()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to check friends!\n");
				throw new RuntimeException(sb.toString());
			}

			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After check friends: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptCheckFriendsCnt++;
		}


	@BenchmarkOperation(name = "CheckProfile",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client checks a user's profile
		 * @throws Exception
		 */
		public void checkProfile() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: checking others profile with"
					+ thisClient.getUsername());


			UserPasswordPair user = getRandomUser();

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + PROFILE_URL + user.getUserName());
			context.recordTime();



			if (sb.toString().contains(user.getUserName()+"</h2>")) {
				logger.fine("Successfully checked the profile: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully checked the profile\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to check profile :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to check profile!\n");
				throw new RuntimeException(sb.toString());
			}

			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After check profile: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptCheckProfileCnt++;
		}


	@BenchmarkOperation(name = "Inbox",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client reads the inbox
		 * @throws Exception
		 */
		public void inbox() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: read inbox with"
					+ thisClient.getUsername());

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + INBOX_URL + thisClient.getUsername());
			context.recordTime();

			if(sb.toString().contains("elgg-object")){
				Scanner sc = new Scanner(sb.toString());
				sc.useDelimiter("elgg-object-");
				sc.next();
				List<String> messages_guids = new ArrayList<String>();
				while(sc.hasNext()){
					String tk = sc.next();
					int idx = tk.indexOf("\"");
					//System.out.println("Next token: "+tk.substring(0, idx));
					messages_guids.add(tk.substring(0, idx));
				}
				thisClient.setMessagesGuids(messages_guids);
			}


			if (sb.toString().contains("Inbox</h2>")) {
				logger.fine("Successfully read the inbox: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully read the inbox\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to read inbox :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to read inbox!\n");
				throw new RuntimeException(sb.toString());
			}

			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After inbox: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptReadInboxCnt++;
		}


	@BenchmarkOperation(name = "Dashboard",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client accesses the dashboard page.
		 * @throws Exception
		 */
		public void Dashboard() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: Dashboard with"
					+ thisClient.getUsername());

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + DASHBOARD_URL);
			context.recordTime();

			if (sb.toString().contains("My New Community")) {
				logger.fine("Successfully loaded dashboard: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully loaded dashboard\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to load dashboard :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to load dashboard!\n");
				throw new RuntimeException(sb.toString());
			}

			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After dashboard: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptDashboardCnt++;
		}

	@BenchmarkOperation(name = "CheckBlog",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client accesses the blog page.
		 * @throws Exception
		 */
		public void CheckBlog() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: CheckBlog with"
					+ thisClient.getUsername());

			int randomIndex = random.nextInt(BLOG_URLS.length);
			String BLOG_URL = BLOG_URLS[randomIndex];
			if (randomIndex != 0){
				BLOG_URL += thisClient.getUsername();
			}

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + BLOG_URL);
			context.recordTime();

			if(sb.toString().contains("elgg-object")){
				Scanner sc = new Scanner(sb.toString());
				sc.useDelimiter("elgg-object-");
				sc.next();
				List<String> blogs_guids = new ArrayList<String>();
				while(sc.hasNext()){
					String tk = sc.next();
					int idx = tk.indexOf("\"");
					//System.out.println("Next blog token: "+tk.substring(0, idx));
					blogs_guids.add(tk.substring(0, idx));
				}
				thisClient.setBlogsGuids(blogs_guids);
			}


			if (sb.toString().contains("blogs</h2>") || sb.toString().contains("Blogs</h2>")) {
				logger.fine("Successfully Checked blogs: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully Checked blogs\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to Check blogs :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to Check blogs!\n");
				throw new RuntimeException(sb.toString());
			}

			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After CheckBlog: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptCheckBlogCnt++;
		}




	@BenchmarkOperation(name = "CheckWire",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client accesses the wire page.
		 * @throws Exception
		 */
		public void CheckWire() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: CheckWire with"
					+ thisClient.getUsername());

			int randomIndex = random.nextInt(WIRE_URLS.length);
			String WIRE_URL = WIRE_URLS[randomIndex];
			if (randomIndex != 0){
				WIRE_URL += thisClient.getUsername();
			}
			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + WIRE_URL);
			context.recordTime();

			if(sb.toString().contains("elgg-object")){
				Scanner sc = new Scanner(sb.toString());
				sc.useDelimiter("elgg-object-");
				sc.next();
				List<String> wires_guids = new ArrayList<String>();
				while(sc.hasNext()){
					String tk = sc.next();
					int idx = tk.indexOf("\"");
					//System.out.println("Next wire token: "+tk.substring(0, idx));
					wires_guids.add(tk.substring(0, idx));
				}
				thisClient.setWiresGuids(wires_guids);
			}

			if (sb.toString().contains("wire posts</h2>")) {
				logger.fine("Successfully Checked wires: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully Checked wires\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to Check wires :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to Check wires!\n");
				throw new RuntimeException(sb.toString());
			}

			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After CheckWire: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptCheckWireCnt++;
		}


	@BenchmarkOperation(name = "CheckActivity",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A client accesses the activity page.
		 * @throws Exception
		 */
		public void CheckActivity() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: CheckActivity with"
					+ thisClient.getUsername());

			int randomIndex = random.nextInt(ACTIVITY_URLS.length);
			String ACTIVITY_URL = ACTIVITY_URLS[randomIndex];
			if (randomIndex != 0){
				ACTIVITY_URL += thisClient.getUsername();
			}
			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + ACTIVITY_URL);
			context.recordTime();

			if (sb.toString().contains("Activity</h2>")) {
				logger.fine("Successfully Checked Activity: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully Checked Activity\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to Check Activity :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to Check Activity!\n");
				throw new RuntimeException(sb.toString());
			}

			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After CheckActivity: __elgg_token=" + thisClient.getElggToken()
			//                + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if (success)
				elggMetrics.attemptCheckActivityCnt++;
		}


	@BenchmarkOperation(name = "BrowsetoElgg",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A logged out client accesses the home page.
		 * @throws Exception
		 */
		public void browseToElgg() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_OUT);

			UserPasswordPair user = getRandomUser();
			thisClient.setGuid(user.getGuid());
			thisClient.setUsername(user.getUserName());
			thisClient.setPassword(user.getPassword());
			thisClient.setFriendsList(new ArrayList<String>());
			thisClient.setMessagesGuids(new ArrayList<String>());
			thisClient.setBlogsGuids(new ArrayList<String>());
			thisClient.setWiresGuids(new ArrayList<String>());


			boolean success = false;
			if (!inited)
				logger.info("Inited thread" + context.getThreadId());
			inited = true;
			logger.fine(context.getThreadId() +" : Doing operation: browseToElgg");


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

			if (sb.toString().contains("My New Community")) {
				logger.fine("Successfully browsed Elgg: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully browsed Elgg, user: "+user.getUserName()+" GUID: "+user.getGuid()+"\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to browse Elgg :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to browse Elgg!\n");
				throw new RuntimeException(sb.toString());
			}


			updateElggTokenAndTs(thisClient, sb, false);
			printErrorMessageIfAny(sb, null);

			success = true;

			if(success){
				elggMetrics.attemptBrowseToElggCnt++;
			}

		}

	@BenchmarkOperation(name = "GetNotifications",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A logged in client accesses the notifications page
		 * @throws Exception
		 */
		public void getNotifications() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);

			boolean success = false;
			logger.fine(context.getThreadId() +" : Doing operation: getNotifications");

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + NOTIFICATIONS_URL + thisClient.getUsername());
			context.recordTime();

			if (sb.toString().contains("Site Notifications")) {
				logger.fine("Successfully got notifications: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully got notifications\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to get notifications :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to get notifications!\n");
				throw new RuntimeException(sb.toString());
			}

			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After GetNotifications: __elgg_token=" + thisClient.getElggToken()
			//              + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;

			if(success){
				elggMetrics.attemptGetNotificationsCnt++;
			}
		}

	@BenchmarkOperation(name = "SendMessage",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		public void sendMessage() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);

			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: sendMessage with"
					+ thisClient.getUsername());

			String postRequest = "__elgg_token=" + thisClient.getElggToken()
				+ "&__elgg_ts=" + thisClient.getElggTs() + "&recipients=&match_on=users"
				+ "&recipients[]=" + getRandomUserGUID() + "&subject="
				+ RandomStringGenerator.generateRandomString(15, Mode.ALPHA)
				+ "&body="
				+ RandomStringGenerator.generateRandomString(15, Mode.ALPHA);


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
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + SEND_MESSAGE_URL,
					postRequest, headers);
			context.recordTime();

			updateElggTokenAndTs(thisClient, sb, false);
			printErrorMessageIfAny(sb, postRequest);

			System.out.println("PRINT: Successfully sent a message");
			success = true;
			if (success)
				elggMetrics.attemptSendMessageCnt++;
		}



	@BenchmarkOperation(name = "PostBlog",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		public void postBlog() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);

			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: postBlog with"
					+ thisClient.getUsername());

			String title = RandomStringGenerator.generateRandomString(15, Mode.ALPHA);
			String body = RandomStringGenerator.generateRandomString(15, Mode.ALPHA);

			String postRequest = "__elgg_token=" + thisClient.getElggToken()
				+ "&__elgg_ts=" + thisClient.getElggTs() + "&title="
				+ title
				+ "&excerpt="
				+ RandomStringGenerator.generateRandomString(15, Mode.ALPHA)
				+ "&description="
				+ body
				+ "&tags=" + "&comments_on=On" + "&access_id=2"
				+ "&status=published" + "&container_guid=" + thisClient.getGuid()
				+ "&guid=" + "&save=Save";


			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			headers.put("Accept-Language", "en-US,en;q=0.5");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/blog");
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
			headers.put("Content-Type", "application/x-www-form-urlencoded");

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + POST_BLOG_URL,
					postRequest); //, headers);
			context.recordTime();

			updateElggTokenAndTs(thisClient, sb, false);
			printErrorMessageIfAny(sb, postRequest);


			if (sb.toString().contains(title) && sb.toString().contains(body)){
				logger.fine("Successfully posted a blog: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully posted a blog\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to post a blog :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to post a blog!\n");
				throw new RuntimeException(sb.toString());
			}

			success = true;
			if (success)
				elggMetrics.attemptPostBlogCnt++;
		}



	@BenchmarkOperation(name = "PostWire",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		public void postWire() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);

			boolean success = false;
			logger.fine(context.getThreadId() + " : Doing operation: postWire with"
					+ thisClient.getUsername());

			String wire = RandomStringGenerator.generateRandomString(15, Mode.ALPHA);
			String postRequest = "__elgg_token=" + thisClient.getElggToken()
				+ "&__elgg_ts=" + thisClient.getElggTs() + "&body=" + wire;


			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			headers.put("Accept-Language", "en-US,en;q=0.5");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/thewire");
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
			headers.put("Content-Type", "application/x-www-form-urlencoded");

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + POST_WIRE_URL,
					postRequest, headers);
			context.recordTime();

			updateElggTokenAndTs(thisClient, sb, false);
			printErrorMessageIfAny(sb, postRequest);

			if (sb.toString().contains(wire)){
				logger.fine("Successfully posted a wire: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully posted a wire\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to post a wire :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to post a wire!\n");
				throw new RuntimeException(sb.toString());
			}

			success = true;
			if (success)
				elggMetrics.attemptPostWireCnt++;
		}



	@BenchmarkOperation(name = "AccessHomePage",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		/**
		 * A logged in client accesses the home page
		 * @throws Exception
		 */
		public void accessHomePage() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);

			boolean success = false;
			logger.fine(context.getThreadId() +" : Doing operation: AccessHomePage");

			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = thisClient.getHttp().fetchURL(hostUrl + ROOT_URL);
			context.recordTime();

			if (sb.toString().contains("My New Community")) {
				logger.fine("Successfully AccessHomePage: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully AccessHomePage\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to Access HomePage :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to Access HomePage!\n");
				throw new RuntimeException(sb.toString());
			}


			updateElggTokenAndTs(thisClient, sb, false);
			//System.out.println("After AccessHomePage: __elgg_token=" + thisClient.getElggToken()
			//           + "&__elgg_ts=" + thisClient.getElggTs());
			printErrorMessageIfAny(sb, null);

			success = true;
			if(success){
				elggMetrics.attemptHomePageCnt++;
			}
		}

	@BenchmarkOperation(name = "DoLogin",
	//max90th = 3.0,
	percentileLimits= {500,1000,2000},
	timing = Timing.MANUAL)
		public void doLogin() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_OUT);

			boolean success = false;
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
				// if a user could not login, try multiple times
				boolean successful_login = false;
				for(int i = 0; i < 10; i++){
					System.out.println("Trying to login again, iter: " + i + "\tusername: " + thisClient.getUsername());
					sb = thisClient.getHttp().fetchURL(hostUrl + LOGIN_URL,
							postRequest, headers);
					if(sb.toString().contains("You have been logged in")){
						successful_login = true;
						logger.fine("Successfully logged in: "+thisClient.getUsername());
						System.out.print("PRINT: Successfully logged in\n");
						break;
					}
				}
				if(!successful_login){
					logger.fine("!!!!!!!!!!!!!!!!!! Failed to log in :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
					System.out.print("PRINT: Failed to login: " + thisClient.getUsername() + "\n");
					throw new RuntimeException(sb.toString());
				}
			}
			thisClient.setLoggedIn(true);
			thisClient.setClientState(ClientState.LOGGED_IN);

			success = true;
			if (success)
				elggMetrics.attemptLoginCnt++;
		}

	/**
	 * Remove friend
	 *
	 * @throws Exception
	 */
	@BenchmarkOperation(name = "RemoveFriend",
	//max90th = 3.0,
	percentileLimits= {500,750,1000},
	timing = Timing.MANUAL)
		public void removeFriend() throws Exception {
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			StringBuilder sb = null;
			logger.fine(context.getThreadId() +" : Doing operation: removeFriend");

			int friendsListSize = thisClient.getFriendsListSize();
			int randomIndex = 0;
			String rmFriendGuid = "";
			if(friendsListSize != 0){
				randomIndex = random.nextInt(friendsListSize);
				rmFriendGuid = thisClient.getFriendsList(randomIndex);
			}
			else{
				rmFriendGuid = getRandomUserGUID();
			}

			thisClient.removeFriendsList(rmFriendGuid);

			String queryString = "friend=" + rmFriendGuid + "&__elgg_ts="
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
			headers.put("Referer", hostUrl + "/profile/"+thisClient.getUsername());
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
			headers.put("Content-Type", "application/x-www-form-urlencoded");
			headers.put("X-Requested-With", "XMLHttpRequest");

			semaphore.acquire();
			context.recordTime();
			sb = thisClient.getHttp().fetchURL(
					hostUrl + DO_REMOVE_FRIEND + "?" + queryString, postString, headers);
			context.recordTime();

			printErrorMessageIfAny(sb, postString);
			success = true;

			System.out.println("PRINT: Successfully removed a friend");
			if (success) {
				elggMetrics.attemptRemoveFriendsCnt++;
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
			assert(thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;
			StringBuilder sb = null;

			logger.fine(context.getThreadId() +" : Doing operation: addFriend");

			thisClient.getHttp().setFollowRedirects(true);

			UserPasswordPair user = getRandomUser();
			String friendeeGuid = user.getGuid();

			thisClient.addFriendsList(friendeeGuid);


			String queryString = "friend=" + friendeeGuid + "&__elgg_ts="
				+ thisClient.getElggTs() + "&__elgg_token="
				+ thisClient.getElggToken();
			String postString = "__elgg_ts="
				+ thisClient.getElggTs() + "&__elgg_token="
				+ thisClient.getElggToken();

			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"application/json, text/javascript, */*; q=0.01");

			headers.put("Accept-Language", "en-US,en;q=0.9");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/profile/"+user.getUserName());
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
			headers.put("X-Requested-With", "XMLHttpRequest");

			semaphore.acquire();
			context.recordTime();
			sb = thisClient.getHttp().fetchURL(
					hostUrl + DO_ADD_FRIEND + "?" + queryString, postString, headers);
			context.recordTime();

			printErrorMessageIfAny(sb, postString);
			System.out.println("PRINT: Successfully added a friend");

			success = true;
			if (success) {
				elggMetrics.attemptAddFriendsCnt++;
			}
		}

	/**
	 * Logging out a logged in user
	 *
	 * @throws Exception
	 */
	@BenchmarkOperation(name = "Logout",
	//max90th = 3.0,
	percentileLimits= {500,750,1000},
	timing = Timing.MANUAL)
		public void logout() throws Exception {
			assert (thisClient.getClientState() == ClientState.LOGGED_IN);
			boolean success = false;

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
			updateElggTokenAndTs(thisClient, sb, false);

			thisClient.setClientState(ClientState.LOGGED_OUT);
			thisClient.setLoggedIn(false);

			System.out.println("PRINT: Successfully logged out");

			success = true;
			if (success) {
				elggMetrics.attemptLogoutCnt++;
			}
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
			assert (thisClient.getClientState() == ClientState.LOGGED_OUT);
			boolean success = false;

			Web20Client tempClient = new Web20Client();
			HttpTransport http;

			http = HttpTransport.newInstance();
			http.setFollowRedirects(true);
			tempClient.setHttp(http);

			logger.fine(context.getThreadId() +" : Doing operation: register");


			// prepare metadata for registering a new user
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
				+ "&friend_guid=&invitecode=";

			Map<String, String> headers = new HashMap<String, String>();
			headers.put("Accept",
					"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			headers.put("Accept-Language", "en-US,en;q=0.5");
			headers.put("Accept-Encoding", "gzip, deflate");
			headers.put("Referer", hostUrl + "/register");
			headers.put("User-Agent",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");
			headers.put("Content-Type", "application/x-www-form-urlencoded");

			// Click on Register link and generate user name and password
			semaphore.acquire();
			context.recordTime();
			StringBuilder sb = tempClient.getHttp().fetchURL(hostUrl + REGISTER_PAGE_URL);
			updateElggTokenAndTs(tempClient, sb, false);

			sb = tempClient.getHttp().fetchURL(hostUrl + DO_REGISTER_URL, postString, headers);
			context.recordTime();


			printErrorMessageIfAny(sb, postString);

			if (sb.toString().contains("Email sent to")) {
				logger.fine("Successfully registered a new user: "+thisClient.getUsername());
				System.out.print("PRINT: Successfully registered a new user\n");
			} else {
				logger.fine("!!!!!!!!!!!!!!!!!! Failed to register :"+thisClient.getUsername()+"!!!!!!!!!!!!!!!!!!!!!");
				System.out.print("PRINT: Failed to register!\n");
				throw new RuntimeException(sb.toString());
			}


			success = true;
			if (success)
				elggMetrics.attemptRegisterCnt++;

		}

	private void printErrorMessageIfAny(StringBuilder sb, String postRequest) {
		String htmlContent = sb.toString();
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

	static class ElggDriverMetrics implements CustomMetrics {

		int attemptLoginCnt = 0;
		int attemptHomePageCnt = 0;
		int attemptPostWallCnt = 0;
		int attemptUpdateActivityCnt = 0;
		int attemptAddFriendsCnt = 0;
		int attemptLogoutCnt = 0;
		int attemptRegisterCnt = 0;
		int attemptCheckActivityCnt = 0;
		int attemptDashboardCnt = 0;
		int attemptBrowseToElggCnt = 0;
		int attemptRemoveFriendsCnt = 0;
		int attemptGetNotificationsCnt = 0;
		int attemptReadInboxCnt = 0;
		int attemptCheckProfileCnt = 0;
		int attemptCheckFriendsCnt = 0;
		int attemptCheckWireCnt = 0;
		int attemptPostWireCnt = 0;
		int attemptSendMessageCnt = 0;
		int attemptReadMessageCnt = 0;
		int attemptCheckBlogCnt = 0;
		int attemptReadMessageFailedCnt = 0;
		int attemptSentMessagesCnt = 0;
		int attemptPostBlogCnt = 0;
		int attemptDeleteMessageCnt = 0;
		int attemptDeleteMessageFailedCnt = 0;
		int attemptLikeCnt = 0;
		int attemptLikeFailedCnt = 0;
		int attemptReplyWireCnt = 0;
		int attemptReplyWireFailedCnt = 0;
		int attemptCommentCnt = 0;
		int attemptCommentFailedCnt = 0;
		int attemptSearchCnt = 0;

		@Override
		public void add(CustomMetrics arg0) {
			ElggDriverMetrics e = (ElggDriverMetrics) arg0;
			this.attemptHomePageCnt += e.attemptHomePageCnt;
			this.attemptLoginCnt += e.attemptLoginCnt;
			this.attemptPostWallCnt += e.attemptPostWallCnt;
			this.attemptUpdateActivityCnt += e.attemptUpdateActivityCnt;
			this.attemptAddFriendsCnt += e.attemptAddFriendsCnt;
			this.attemptLogoutCnt += e.attemptLogoutCnt;
			this.attemptRegisterCnt += e.attemptRegisterCnt;
			this.attemptCheckActivityCnt += e.attemptCheckActivityCnt;
			this.attemptDashboardCnt += e.attemptDashboardCnt;
			this.attemptBrowseToElggCnt += e.attemptBrowseToElggCnt;
			this.attemptRemoveFriendsCnt += e.attemptRemoveFriendsCnt;
			this.attemptGetNotificationsCnt += e.attemptGetNotificationsCnt;
			this.attemptReadInboxCnt += e.attemptReadInboxCnt;
			this.attemptCheckProfileCnt += e.attemptCheckProfileCnt;
			this.attemptCheckFriendsCnt += e.attemptCheckFriendsCnt;
			this.attemptCheckWireCnt += e.attemptCheckWireCnt;
			this.attemptPostWireCnt += e.attemptPostWireCnt;
			this.attemptSendMessageCnt += e.attemptSendMessageCnt;
			this.attemptReadMessageCnt += e.attemptReadMessageCnt;
			this.attemptCheckBlogCnt += e.attemptCheckBlogCnt;
			this.attemptReadMessageFailedCnt += e.attemptReadMessageFailedCnt;
			this.attemptSentMessagesCnt += e.attemptSentMessagesCnt;
			this.attemptPostBlogCnt += e.attemptPostBlogCnt;
			this.attemptDeleteMessageCnt += e.attemptDeleteMessageCnt;
			this.attemptDeleteMessageFailedCnt += e.attemptDeleteMessageFailedCnt;
			this.attemptLikeCnt += e.attemptLikeCnt;
			this.attemptLikeFailedCnt += e.attemptLikeFailedCnt;
			this.attemptReplyWireCnt += e.attemptReplyWireCnt;
			this.attemptReplyWireFailedCnt += e.attemptReplyWireFailedCnt;
			this.attemptCommentCnt += e.attemptCommentCnt;
			this.attemptCommentFailedCnt += e.attemptCommentFailedCnt;
			this.attemptSearchCnt += e.attemptSearchCnt;

		}

		@Override
		public Element[] getResults() {
			Element[] el = new Element[30];
			el[0] = new Element();
			el[0].description = "Number of times home page was actually attempted to be accessed.";
			el[0].passed = true;
			el[0].result = "" + this.attemptHomePageCnt;
			el[1] = new Element();
			el[1].description = "Number of times login was actually attempted.";
			el[1].passed = true;
			el[1].result = "" + this.attemptLoginCnt;
			el[2] = new Element();
			el[2].description = "Number of times add friends was actually attempted.";
			el[2].passed = true;
			el[2].result = "" + this.attemptAddFriendsCnt;
			el[3] = new Element();
			el[3].description = "Number of times logout was actually attempted.";
			el[3].passed = true;
			el[3].result = "" + this.attemptLogoutCnt;
			el[4] = new Element();
			el[4].description = "Number of times register was actually attempted.";
			el[4].passed = true;
			el[4].result = "" + this.attemptRegisterCnt;
			el[5] = new Element();
			el[5].description = "Number of times check activity was actually attempted.";
			el[5].passed = true;
			el[5].result = "" + this.attemptCheckActivityCnt;
			el[6] = new Element();
			el[6].description = "Number of times dashboard was actually attempted.";
			el[6].passed = true;
			el[6].result = "" + this.attemptDashboardCnt;
			el[7] = new Element();
			el[7].description = "Number of times BrowseToElgg was actually attempted.";
			el[7].passed = true;
			el[7].result = "" + this.attemptBrowseToElggCnt;
			el[8] = new Element();
			el[8].description = "Number of times removeFriends was actually attempted.";
			el[8].passed = true;
			el[8].result = "" + this.attemptRemoveFriendsCnt;
			el[9] = new Element();
			el[9].description = "Number of times GetNotifications was actually attempted.";
			el[9].passed = true;
			el[9].result = "" + this.attemptGetNotificationsCnt;
			el[10] = new Element();
			el[10].description = "Number of times readInbox was actually attempted.";
			el[10].passed = true;
			el[10].result = "" + this.attemptReadInboxCnt;
			el[11] = new Element();
			el[11].description = "Number of times checkProfile was actually attempted.";
			el[11].passed = true;
			el[11].result = "" + this.attemptCheckProfileCnt;
			el[12] = new Element();
			el[12].description = "Number of times checkFriends was actually attempted.";
			el[12].passed = true;
			el[12].result = "" + this.attemptCheckFriendsCnt;
			el[13] = new Element();
			el[13].description = "Number of times checkWire was actually attempted.";
			el[13].passed = true;
			el[13].result = "" + this.attemptCheckWireCnt;
			el[14] = new Element();
			el[14].description = "Number of times postWire was actually attempted.";
			el[14].passed = true;
			el[14].result = "" + this.attemptPostWireCnt;
			el[15] = new Element();
			el[15].description = "Number of times sendMessage was actually attempted.";
			el[15].passed = true;
			el[15].result = "" + this.attemptSendMessageCnt;
			el[16] = new Element();
			el[16].description = "Number of times readMessage was successfully attempted.";
			el[16].passed = true;
			el[16].result = "" + this.attemptReadMessageCnt;
			el[17] = new Element();
			el[17].description = "Number of times checkBlog was actually attempted.";
			el[17].passed = true;
			el[17].result = "" + this.attemptCheckBlogCnt;
			el[18] = new Element();
			el[18].description = "Number of times readMessage was actually attempted.";
			el[18].passed = true;
			el[18].result = "" + this.attemptReadMessageFailedCnt;
			el[19] = new Element();
			el[19].description = "Number of times sentMessages was not attempted.";
			el[19].passed = true;
			el[19].result = "" + this.attemptSentMessagesCnt;
			el[20] = new Element();
			el[20].description = "Number of times postBlog was actually attempted.";
			el[20].passed = true;
			el[20].result = "" + this.attemptPostBlogCnt;
			el[21] = new Element();
			el[21].description = "Number of times deleteMessage was successfully attempted.";
			el[21].passed = true;
			el[21].result = "" + this.attemptDeleteMessageCnt;
			el[22] = new Element();
			el[22].description = "Number of times deleteMessage was not attempted.";
			el[22].passed = true;
			el[22].result = "" + this.attemptDeleteMessageFailedCnt;
			el[23] = new Element();
			el[23].description = "Number of times like was successfully attempted.";
			el[23].passed = true;
			el[23].result = "" + this.attemptLikeCnt;
			el[24] = new Element();
			el[24].description = "Number of times like was not attempted.";
			el[24].passed = true;
			el[24].result = "" + this.attemptLikeFailedCnt;
			el[25] = new Element();
			el[25].description = "Number of times replyWire was successfully attempted.";
			el[25].passed = true;
			el[25].result = "" + this.attemptReplyWireCnt;
			el[26] = new Element();
			el[26].description = "Number of times replyWire was not attempted.";
			el[26].passed = true;
			el[26].result = "" + this.attemptReplyWireFailedCnt;
			el[27] = new Element();
			el[27].description = "Number of times comment was successfully attempted.";
			el[27].passed = true;
			el[27].result = "" + this.attemptCommentCnt;
			el[28] = new Element();
			el[28].description = "Number of times comment was not attempted.";
			el[28].passed = true;
			el[28].result = "" + this.attemptCommentFailedCnt;
			el[29] = new Element();
			el[29].description = "Number of times search was acctually attempted.";
			el[29].passed = true;
			el[29].result = "" + this.attemptSearchCnt;


			return el;
		}

		public Object clone() {
			ElggDriverMetrics clone = new ElggDriverMetrics();
			clone.attemptHomePageCnt = this.attemptHomePageCnt;
			clone.attemptLoginCnt = this.attemptLoginCnt;
			clone.attemptPostWallCnt = this.attemptPostWallCnt;
			clone.attemptUpdateActivityCnt = this.attemptUpdateActivityCnt;
			clone.attemptAddFriendsCnt = this.attemptAddFriendsCnt;
			clone.attemptLogoutCnt = this.attemptLogoutCnt;
			clone.attemptRegisterCnt = this.attemptRegisterCnt;
			clone.attemptCheckActivityCnt = this.attemptCheckActivityCnt;
			clone.attemptDashboardCnt = this.attemptDashboardCnt;
			clone.attemptBrowseToElggCnt = this.attemptBrowseToElggCnt;
			clone.attemptRemoveFriendsCnt = this.attemptRemoveFriendsCnt;
			clone.attemptGetNotificationsCnt = this.attemptGetNotificationsCnt;
			clone.attemptReadInboxCnt = this.attemptReadInboxCnt;
			clone.attemptCheckProfileCnt = this.attemptCheckProfileCnt;
			clone.attemptCheckFriendsCnt = this.attemptCheckFriendsCnt;
			clone.attemptCheckWireCnt = this.attemptCheckWireCnt;
			clone.attemptPostWireCnt = this.attemptPostWireCnt;
			clone.attemptSendMessageCnt = this.attemptSendMessageCnt;
			clone.attemptReadMessageCnt = this.attemptReadMessageCnt;
			clone.attemptCheckBlogCnt = this.attemptCheckBlogCnt;
			clone.attemptReadMessageFailedCnt = this.attemptReadMessageFailedCnt;
			clone.attemptSentMessagesCnt = this.attemptSentMessagesCnt;
			clone.attemptPostBlogCnt = this.attemptPostBlogCnt;
			clone.attemptDeleteMessageCnt = this.attemptDeleteMessageCnt;
			clone.attemptDeleteMessageFailedCnt = this.attemptDeleteMessageFailedCnt;
			clone.attemptLikeCnt = this.attemptLikeCnt;
			clone.attemptLikeFailedCnt = this.attemptLikeFailedCnt;
			clone.attemptReplyWireCnt = this.attemptReplyWireCnt;
			clone.attemptReplyWireFailedCnt = this.attemptReplyWireFailedCnt;
			clone.attemptCommentCnt = this.attemptCommentCnt;
			clone.attemptCommentFailedCnt = this.attemptCommentFailedCnt;
			clone.attemptSearchCnt = this.attemptSearchCnt;

			return clone;
		}
	}
}

