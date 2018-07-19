package workload.driver;

/**
 * This class maintains the mapping between the user and her password.
 * 
 * @author Tapti Palit
 *
 */
public class UserPasswordPair {

	private String guid;
	private String userName;
	private String password;
	
	public UserPasswordPair(String guid, String userName, String password) {
		this.setGuid(guid);
		this.userName = userName;
		this.password = password;
	}
	
	public UserPasswordPair() {
	}

	public String getUserName() {
		return userName;
	}
	public void setUserName(String userName) {
		this.userName = userName;
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
