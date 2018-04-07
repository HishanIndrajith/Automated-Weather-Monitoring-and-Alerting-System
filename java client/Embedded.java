
import java.net.*;
import java.io.*;
import java.util.*;

public class  Embedded {
    public static void main(String[] args) throws Exception {
	    try {
	    	int location = Integer.parseInt(args[0]);
	    	int no=0;
		    while(true){
		    	int temp=(int)(50*Math.random());
		    	int hum = (int)(50*Math.random());
		    	int wind_speed = (int)(160*Math.random());
		    	int is_raining = (int)(1.9*Math.random());
		    	int current_rainfall = (int)(150*Math.random());
		    	String url_s = "http://localhost/weather/dataset1.php?temp="+temp+"&hum="+hum+"&wind_speed="+wind_speed+"&is_raining="+is_raining+"&current_rainfall="+current_rainfall+"&location="+location;
		        URL url = new URL(url_s);
		        URLConnection yc = url.openConnection();
		        BufferedReader in = new BufferedReader(new InputStreamReader(
		                                    yc.getInputStream()));
		        String inputLine;

				System.out.println("data no = "+no+"  values>> temp = "+temp+ ", hum = " +hum+ ", wind speed = " +wind_speed+ ", is_raining = " + is_raining+", current_rainfall = " + current_rainfall);
		        while ((inputLine = in.readLine()) != null)
		            System.out.println(inputLine);
		        in.close();
		        try {
				//sleep 2 minutes
					Thread.sleep(60000);
				
				} catch (InterruptedException e) {
					e.printStackTrace();
				}
				no++;
		    }
	    } 
	    catch (MalformedURLException e) { 
	        System.out.println("Error in URL");
	    } 
	    catch (IOException e) {   
	        System.out.println("URL not exists");
	    }
        
    }
}
