package workload.driver;

import java.util.List;

import com.sun.faban.driver.HttpTransport;

/**
 * This class contains all details of one particular Elgg user.
 * 
 * @author Tapti Palit
 *
 */
public class Web20Client {
	
	private String elggToken;
	private String elggTs;
	private String username;
	private String password;
	private String email;
	private String guid;

	private String numActivities;
	
	private boolean loggedIn;

	private HttpTransport http;
	
	private List<String> chatSessionList; // List of guids of chat sessions of this client. 
	
	public enum ClientState {
		LOGGED_IN,
		LOGGED_OUT
	};
	
	private ClientState clientState;
	
	public Web20Client() {
		this.clientState = ClientState.LOGGED_OUT;
	}
	
	public String getElggToken() {
		return elggToken;
	}
	
	public void setElggToken(String elggToken) {
		this.elggToken = elggToken;
	}
	
	public String getElggTs() {
		return elggTs;
	}
	
	public void setElggTs(String elggTs) {
		this.elggTs = elggTs;
	}
	
	public String getUsername() {
		return username;
	}
	
	public void setUsername(String username) {
		this.username = username;
	}
	
	public String getPassword() {
		return password;
	}
	
	public void setPassword(String password) {
		this.password = password;
	}
	
	public HttpTransport getHttp() {
		return http;
	}
	
	public void setHttp(HttpTransport http) {
		this.http = http;
	}
	
	public String getGuid() {
		return guid;
	}
	
	public void setGuid(String guid) {
		this.guid = guid;
	}
	
	public String getEmail() {
		return email;
	}
	
	public void setEmail(String email) {
		this.email = email;
	}

	public ClientState getClientState() {
		return this.clientState;
	}
	
	public void setClientState(ClientState clientState) {
		this.clientState = clientState;
	}

	public List<String> getChatSessionList() {
		return chatSessionList;
	}

	public void setChatSessionList(List<String> chatSessionList) {
		this.chatSessionList = chatSessionList;
	}

	public boolean isLoggedIn() {
		return loggedIn;
	}

	public void setLoggedIn(boolean loggedIn) {
		this.loggedIn = loggedIn;
	}

	public String getNumActivities() {
		return numActivities;
	}

	public void setNumActivities(String numActivities) {
		this.numActivities = numActivities;
	}
}
