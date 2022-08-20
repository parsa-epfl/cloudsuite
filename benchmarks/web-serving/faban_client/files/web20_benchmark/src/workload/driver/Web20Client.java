package workload.driver;

import java.util.List;

import com.sun.faban.driver.HttpTransport;

/**
 * This class contains all details of one particular Elgg user.
 * 
 * @author Tapti Palit
 * @author Ali Ansari
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
	
	private List<String> friendsList;
	private List<String> messages_guids;
	private List<String> blogs_guids;
	private List<String> wires_guids;

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

	public void addFriendsList(String guid){
		this.friendsList.add(guid);
	}

	public void removeFriendsList(String guid){
		this.friendsList.remove(guid);
	}

	public void setFriendsList(List<String> friendsList){
		this.friendsList = friendsList;
	}

	public String getFriendsList(int index){
		return this.friendsList.get(index);
	}

	public int getFriendsListSize(){
		return this.friendsList.size();
	}

	public void setMessagesGuids(List<String> messages_guids){
		this.messages_guids = messages_guids;
	}

	public int getMessagesGuidsSize(){
		return this.messages_guids.size();
	}

	public String getMessagesGuids(int index){
		return this.messages_guids.get(index);
	}

	public void removeMessagesGuids(String item){
		this.messages_guids.remove(item);
	}

	public void setBlogsGuids(List<String> blogs_guids){
		this.blogs_guids = blogs_guids;
	}

	public int getBlogsGuidsSize(){
		return this.blogs_guids.size();
	}

	public String getBlogsGuids(int index){
		return this.blogs_guids.get(index);
	}

	public void setWiresGuids(List<String> wires_guids){
		this.wires_guids = wires_guids;
	}

	public int getWiresGuidsSize(){
		return this.wires_guids.size();
	}

	public String getWiresGuids(int index){
		return this.wires_guids.get(index);
	}

}
