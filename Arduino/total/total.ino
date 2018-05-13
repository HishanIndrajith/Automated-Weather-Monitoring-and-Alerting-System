// variable to store the value read
#include <dht.h>
#include <SoftwareSerial.h>

SoftwareSerial mySerial(9, 10);//Rx,Tx
dht DHT;
#define DHT11_PIN 7

int analogPin = 1;     
                       // outside leads to ground and +5V
int val = 0;   
const float Pi = 3.14159265359;
int startT=millis();
int endT=millis();
int x=0;

const int trigPin = 6;// orange
const int echoPin = 5;//yellow
// defines variables
long duration;
int distance;

int rainPin = A0;
// you can adjust the threshold value
int thresholdValue = 500;
////////
int i=0;

float windSpeed=0;
float rainfall=0;
int isRainning=0;
float temperature=0;
float humidity=0;

void setup() {
  pinMode(trigPin, OUTPUT); // Sets the trigPin as an Output
  pinMode(echoPin, INPUT); // Sets the echoPin as an Input
  pinMode(rainPin, INPUT);
  mySerial.begin(9600);   // Setting the baud rate of GSM Module 
  Serial.begin(9600);  

}

void loop() {
  i++;
  //IR
 val = analogRead(analogPin);   
  //delay(10);//delay 10 milli
  delay(10);
  if(val>900){
    x=1;
     //Serial.println(">500");
  }
  else if(val<60 && x==1){
    x=0;
    startT=endT;
    endT=millis();
    int gap = endT-startT;
    float speed = (1800*Pi)/gap;
    if(speed>windSpeed){
      windSpeed=speed;
    }
    //Serial.print("wind speed = ");
    //Serial.println(speed);
  }

  //sonar 
  // Clears the trigPin
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    // Sets the trigPin on HIGH state for 10 micro seconds
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);
    // Reads the echoPin, returns the sound wave travel time in microseconds
    duration = pulseIn(echoPin, HIGH);
    // Calculating the distance
    distance= duration*0.034/2;
    // Prints the distance on the Serial Monitor
    rainfall=(44-distance)*2; //change this

    //rainning
    // read the input on analog pin 0:
    int sensorValue = analogRead(rainPin);
    if(sensorValue < thresholdValue){
      isRainning=1;
    }

    //temp
    if(i>6000){ //no of 10ms
      int chk = DHT.read11(DHT11_PIN);
      float temp = DHT.temperature;
      if(temperature>=0 && temperature<=60){
        temperature=temp;
      }
      float hum = DHT.humidity;
      if(humidity>=0 && humidity<=100){
        humidity=hum;
      }
        Serial.println("==========================================================");
        Serial.print("Wind Speed = ");
        Serial.println(windSpeed,1);
      
        Serial.print("Rain Fall = ");
        Serial.println(rainfall,1);

        
        Serial.print("is Rainning = ");
        Serial.println(isRainning,1);

        
        Serial.print("Temperature = ");
        Serial.println(temperature,1);
        
        Serial.print("Humidity = ");
        Serial.println(humidity,1);

        String wind = String(windSpeed,2);
        String rain = String(rainfall,2);
        String isRain = String(isRainning);
        String tem = String(temperature,2);
        String humi = String(humidity,2);
        data_set1(tem,humi,wind,isRain,rain);
        windSpeed=0;
        rainfall=0;
        isRainning=0;
        temperature=0;
        humidity=0;
        i=0;
    }
    
    
}

  void data_set1(String temp,String hum,String wind_speed,String is_raining,String current_rainfall)
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
  mySerial.println("AT+HTTPPARA=\"URL\",\"http://digitalthings.comlu.com/weather/dataset1.php?temp="+temp+"&hum="+hum+"&wind_speed="+wind_speed+"&is_raining="+is_raining+"&current_rainfall="+current_rainfall+"&location=1\"");
  delay(1000);
  mySerial.println("AT+HTTPPARA=\"CID\",1");
  delay(1000);
  mySerial.println("AT+HTTPACTION=0");
  delay(1000);
  mySerial.println("AT+HTTPTERM"); 
  delay(1000);
 }
