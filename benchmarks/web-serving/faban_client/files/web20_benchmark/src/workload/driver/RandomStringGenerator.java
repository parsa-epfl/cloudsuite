package workload.driver;

public class RandomStringGenerator {

	public static enum Mode {
		ALPHA, ALPHANUMERIC, NUMERIC
	}

	public static String generateRandomString(int length, Mode mode)
			throws Exception {

		StringBuffer buffer = new StringBuffer();
		String characters = "";

		switch (mode) {

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
