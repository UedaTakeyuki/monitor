# coding:utf-8 Copy Right Atelier Grenouille © 2015 -
import sys
import ConfigParser
from m2x.client import M2XClient

def sendToM2X(serial_id, name, data):
  # 設定の取得
  ini = ConfigParser.SafeConfigParser()
  ini.read("uploads/"+serial_id+"/m2xconfig.ini") # 起動時に設定を取得
  client_key_str=ini.get("device", "m2x_client_key")
  device_str=ini.get("device", "m2x_device")
  #print client_key_str
  #print device_str

	# M2Xの設定
  try:
    #client = M2XClient(key='16909283a7c638d9e311f23b76d3ec0b')
    #device = client.device('b821e9c48103db7454069782c0bdc0bd')
    client = M2XClient(client_key_str)
    device = client.device(device_str)
    #client.stream_temperature = device.stream('temp') 
    #client.stream_humidity = device.stream('humidity')
    #client.stream_HumidityDeficit = device.stream('HumidityDeficit')
    #client.stream_lux = device.stream('lux')
    #client.stream_co2 = device.stream('co2')
  except:
    print "M2X not connected:", sys.exc_info()[0], sys.exc_info()[1]

  # データ送信
  try:
    stream = device.stream(name)
    stream.add_value(data)

  except:
    print "M2X send error:", sys.exc_info()[0], sys.exc_info()[1]

if __name__ == '__main__':
#    print sendToM2X("00000000790f4c7c", "temp", "20.5")
     print sendToM2X(sys.argv[1],sys.argv[2],sys.argv[3])