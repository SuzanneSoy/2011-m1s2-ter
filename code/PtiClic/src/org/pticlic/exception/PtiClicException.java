package org.pticlic.exception;

import java.io.Serializable;

import com.google.gson.Gson;

public class PtiClicException extends Exception {

	private static final long serialVersionUID = 1L;
	private Error error;
	
	public static class Error implements Serializable {
		
		private static final long serialVersionUID = 1L;
		private int num;
		private String msg;
		
		public Error() {}
		
		public Error(int num, String msg) {
			this.num = num;
			this.msg = msg;
		}

		public int getNum() {
			return num;
		}

		public String getMsg() {
			return msg;
		}
	}
	
	public PtiClicException(Error error) {
		this.error = error;
	}
	
	public PtiClicException(int num, String msg) {
		this.error = new Error(num, msg);
	}
	
	public PtiClicException(String json) {
		Gson gson = new Gson();
		error = gson.fromJson(json, Error.class);
	}

	public String getMessage() {
		return "Erreur numero: " + error.getNum() + "\n" + error.getMsg();
	}

}
