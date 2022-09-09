package setup;

import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.PrintWriter;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Properties;
import com.sun.faban.driver.HttpTransport;

/**
 * Adds users to the Elgg website.
 * 
 * @author Tapti Palit
 *
 */
public class UserGenerator {
	
	private String hostURL;

	private Properties properties;
	private List<UserEntity> userList;
	private Pair<String, String> tokenTsPair;
	
	public UserGenerator(String host){
		hostURL = host;
		tokenTsPair = new Pair<String, String>();
	}
	
	private void updateElggTokenAndTs(Pair<String, String> p, StringBuilder sb) {
        // Get the token values
        int elggTokenStartIndex = sb.indexOf("\"__elgg_token\":\"") + "\"__elgg_token\":\"".length();
        int elggTokenEndIndex = sb.indexOf("\"", elggTokenStartIndex);
        String elggToken = sb.substring(elggTokenStartIndex, elggTokenEndIndex);
        //System.out.println("Elgg Token = "+elggToken);
        
        int elggTsStartIndex = sb.indexOf("\"__elgg_ts\":") + "\"__elgg_ts\":".length();
        int elggTsEndIndex = sb.indexOf(",", elggTsStartIndex);
        String elggTs = sb.substring(elggTsStartIndex, elggTsEndIndex);
        //System.out.println("Elgg Ts = "+elggTs);
        
        p.setValue1(elggToken);
        p.setValue2(elggTs);
	}

	private void loadProperties() throws IOException {
		// Read properties
		properties = new Properties();
		String propFileName = "usersetup.properties";
		
		InputStream inputStream = new FileInputStream(System.getenv("FABAN_HOME")+'/'+propFileName);
		
		if (null != inputStream) {
			properties.load(inputStream);
		} else {
			throw new FileNotFoundException("usersetup.properties file not found.");
		}
	}
	
	private void generateUsers() throws Exception {
		int numUsers = Integer.parseInt(properties.getProperty("num_users").trim());
		int usernameLen = Integer.parseInt(properties.getProperty("username_len").trim());
		int passwordLen = Integer.parseInt(properties.getProperty("password_len").trim());
		
		String outputFile = properties.getProperty("output_file").trim();
		
		userList = new ArrayList<UserEntity>();
		
		for (int i = 0; i<numUsers; i++) {
			String userName = RandomStringGenerator.generateRandomString(usernameLen, RandomStringGenerator.Mode.ALPHA);
			String password = RandomStringGenerator.generateRandomString(passwordLen, RandomStringGenerator.Mode.ALPHANUMERIC);
			String userEmail = userName+"@gmail.com";
			String displayName = userName;
			UserEntity entity = new UserEntity();
			entity.setDisplayName(displayName);
			entity.setEmail(userEmail);
			entity.setPassword(password);
			entity.setUserName(userName);
			userList.add(entity);
		}
	}
	
	private void createUsers() throws Exception {
		int i = 0;
		
		HttpTransport http = HttpTransport.newInstance();
		http.setFollowRedirects(true);
		http.addTextType("application/xhtml+xml");
		http.addTextType("application/xml");
		http.addTextType("q=0.9,*/*");
		http.addTextType("q=0.8");
		
		/* Login as Admin */
		
		StringBuilder sb = http.fetchURL(hostURL+"/");
		
		// Get the token values
		updateElggTokenAndTs(tokenTsPair, sb);

		String loginPostRequest="__elgg_token="+tokenTsPair.getValue1()+"&__elgg_ts="+tokenTsPair.getValue2()+"&username=admin&password=adminadmin";
		
		Map<String, String> headers = new HashMap<String, String>();
		headers.put("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
		headers.put("Accept-Language", "en-US,en;q=0.5");
		// commented by a_ansaarii, some server responses become unreadable, I tested different encodings and decided to comment the following line
		//headers.put("Accept-Encoding", "*");
		headers.put("Referer", hostURL+"/admin/users/add");
		headers.put("User-Agent", "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0");

		sb = http.fetchURL(hostURL+"/action/login", loginPostRequest, headers);
		sb = http.fetchURL(hostURL+"/activity");
		// Update token
		updateElggTokenAndTs(tokenTsPair, sb);
		
		// Weird response from the server when fetching this page!	
		headers.put("Referer", hostURL+"/activity");
		sb = http.fetchURL(hostURL+"/admin", headers);
		updateElggTokenAndTs(tokenTsPair, sb);
	
		String outputFile = System.getenv("FABAN_HOME")+"/"+properties.getProperty("output_file").trim();
		PrintWriter pw = new PrintWriter(new FileOutputStream(new File(outputFile), true));
	
		i = 0;
		for (UserEntity user: userList) {
			headers.put("Referer", hostURL+"/admin");
			sb = http.fetchURL(hostURL+"/admin/users/add", headers);
			updateElggTokenAndTs(tokenTsPair, sb);		
			
			String postRequest = "__elgg_token="+tokenTsPair.getValue1()+"&__elgg_ts="
					+tokenTsPair.getValue2()+"&name="+user.getDisplayName()+"&username="+user.getUserName()+"&email="+user.getEmail()+"&password="+user.getPassword()
					+"&password2="+user.getPassword()+"&admin=0";
			headers.put("Referer", hostURL+"/admin/users/add");
			sb = http.fetchURL(hostURL+"/action/useradd", postRequest, headers);
			int startIndex = sb.indexOf("GUID#")+"GUID#".length();
			int endIndex = sb.indexOf("#", startIndex);
			String guid = sb.substring(startIndex, endIndex);
			user.setGuid(guid);
			System.out.println("User"+i+++" generated.");
			pw.append(user.getGuid()+" "+user.getUserName()+" "+user.getPassword()+"\n");
			pw.flush();
		}
		//pw.flush();
		pw.close();
	}
	
	public static void main(String[] args) throws Exception {
		UserGenerator gen = new UserGenerator(args[0]);
		gen.loadProperties();
		gen.generateUsers();
		gen.createUsers();
		//gen.writeUserFile();
	}

	private void writeUserFile() throws FileNotFoundException {
	        String outputFile = System.getenv("FABAN_HOME")+"/"+properties.getProperty("output_file").trim();
		
		
		PrintWriter pw = new PrintWriter(outputFile);
		for (UserEntity entity: userList) {
			pw.println(entity.getGuid()+" "+entity.getUserName()+" "+entity.getPassword());
		}
		pw.flush();
		pw.close();

		
	}
	
	/*
	public static void main(String[] pp) throws Exception {
		List<UserPasswordPair> userPasswordList = new ArrayList<UserPasswordPair>();
		File fXmlFile = new File("./users.xml");
		DocumentBuilderFactory dbFactory = DocumentBuilderFactory.newInstance();
		DocumentBuilder dBuilder = dbFactory.newDocumentBuilder();
		Document doc = dBuilder.parse(fXmlFile);
		
		Element properties = doc.getDocumentElement();
		NodeList propList = properties.getChildNodes();
		for (int i = 0; i < propList.getLength(); i++) {
			Node property =  propList.item(i);
			if (property instanceof Node) {
				Node element = (Node) property;
				if(!"#text".equals(element.getNodeName())) {
					UserPasswordPair pair = new UserPasswordPair();
					NodeList elemChildren = element.getChildNodes();
					for (int j = 0; j < elemChildren.getLength(); j++) {
						Node property2 = elemChildren.item(j);
						if (property2 instanceof Node) {
							Node element2 = (Node) property2;
							if ("username".equals(element2.getNodeName())) {
								pair.setUserName(element2.getTextContent());
							} else if ("password".equals(element2.getNodeName())) {
								pair.setPassword(element2.getTextContent());
							}
						}
					}
					userPasswordList.add(pair);
				}
			}
		}
		
		File outFile = new File("users.txt");
		PrintWriter pw = new PrintWriter(outFile);
		for (UserPasswordPair up: userPasswordList) {
			pw.println(up.getUserName()+" "+up.getPassword());
		}
		pw.close();
	}
	*/
}

class RandomStringGenerator {
	
	public static enum Mode {
	    ALPHA, ALPHANUMERIC, NUMERIC 
	}
	
	public static String generateRandomString(int length, Mode mode) throws Exception {

		StringBuffer buffer = new StringBuffer();
		String characters = "";

		switch(mode){
		
		case ALPHA:
			characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
			break;
		
		case ALPHANUMERIC:
			characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
			break;
	
		case NUMERIC:
			characters = "1234567890";
		    break;
		}
		
		int charactersLength = characters.length();

		for (int i = 0; i < length; i++) {
			double index = Math.random() * charactersLength;
			buffer.append(characters.charAt((int) index));
		}
		return buffer.toString();
	}
}

class UserEntity {
	
	private String displayName;
	private String userName;
	private String email;
	private String password;
	private String guid;
	
	public String getDisplayName() {
		return displayName;
	}
	public void setDisplayName(String displayName) {
		this.displayName = displayName;
	}
	public String getUserName() {
		return userName;
	}
	public void setUserName(String userName) {
		this.userName = userName;
	}
	public String getEmail() {
		return email;
	}
	public void setEmail(String email) {
		this.email = email;
	}
	public String getPassword() {
		return password;
	}
	public void setPassword(String password) {
		this.password = password;
	}
	public String getGuid() {
		return guid;
	}
	public void setGuid(String guid) {
		this.guid = guid;
	}
	
}

class Pair<E, F> {
	private E value1;
	private F value2;
	
	Pair() {
		
	}
	
	Pair(E value1, F value2) {
		this.value1 = value1;
		this.value2 = value2;
	}
	
	public E getValue1() {
		return value1;
	}
	public void setValue1(E value1) {
		this.value1 = value1;
	}
	public F getValue2() {
		return value2;
	}
	public void setValue2(F value2) {
		this.value2 = value2;
	}
	
	
}
