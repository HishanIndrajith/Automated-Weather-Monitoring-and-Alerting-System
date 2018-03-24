#include <SoftwareSerial.h>

SoftwareSerial mySerial(9, 10);

void setup()

{

  mySerial.begin(9600);   // Setting the baud rate of GSM Module 

  Serial.begin(9600);    // Setting the baud rate of Serial Monitor (Arduino)

  delay(100);

}

void loop()

{

  if (Serial.available()>0){

    switch(Serial.read())
  
    {
      
      case 'g':
        Serial.write("Beginning to send data...\n");
        data_set1("65","55","2","20");
        Serial.write("Data sent successfull...\n");
        break;

    }
  }
}


  void data_set1(String temp,String hum,String wind_speed,String wind_dir)
 {
  mySerial.println("AT+CGATT=1");
  delay(1000);
  mySerial.println("AT+CREG?");
  delay(1000);
  mySerial.println("AT+SAPBR=1,1");
  delay(1000);
  mySerial.println("AT+SAPBER=2,1");
  delay(1000);
  mySerial.println("AT+HTTPINIT");
  delay(1000);
  mySerial.println("AT+HTTPPARA=\"URL\",\"http://digitalthings.comlu.com/weather/dataset1.php?temp="+temp+"&hum="+hum+"&wind_speed="+wind_speed+"&wind_dir="+wind_dir+"&location = 1\"");
  delay(1000);
  mySerial.println("AT+HTTPPARA=\"CID\",1");
  delay(1000);
  mySerial.println("AT+HTTPACTION=0");
  delay(1000);
  mySerial.println("AT+HTTPTERM"); 
  delay(1000);
 }

