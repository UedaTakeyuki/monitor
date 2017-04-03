# coding:utf-8 Copy Right Atelier Grenouille © 2015 -
#
# hmqp:
# 
# A mqtt bridge of http for a limited purpose.
#
# refered http://tdoc.info/blog/2014/09/25/mqtt_python.html

import paho.mqtt.client as mqtt
import json
import requests

# 定数
server_url_base = "http://localhost/" # Server 毎に変更が必用
url_data = server_url_base + 'postdata.php'

def on_connect(client, userdata, rc):
    print("Connected with result code "+str(rc))
    client.subscribe("gal/gal4/#")

def on_message(client, userdata, msg):
    print(msg.topic+" "+str(msg.payload))
    payload = json.loads(msg.payload)
    r = requests.post(url_data, data=payload, timeout=10, verify=False)


if __name__ == '__main__':

    client = mqtt.Client(client_id="ueda-spam", clean_session=True, protocol=mqtt.MQTTv311)
    client.on_connect = on_connect
    client.on_message = on_message

    client.connect("127.0.0.1", 1883, 60)

    client.loop_forever()
