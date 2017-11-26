	var client; // MQTTのクライアントです
	var clientId = "clientid-test"; // ClientIDを指定します。

	function connect(){
	    var user_name = "UedaTakeyuki@github";
	    var pass = "tcJy9P8z61ztwF6K";
	    var wsurl = "ws://free.mqtt.shiguredo.jp:8080/mqtt";

	    // WebSocketURLとClientIDからMQTT Clientを作成します
	    client = new Paho.MQTT.Client(wsurl, clientId);
//	    client = new Paho.MQTT.Client("free.mqtt.shiguredo.jp",8080, "/mqtt", clientId);

	    // connectします
	    client.connect({userName: user_name, password: pass, onSuccess:onConnect, onFailure: failConnect});

	}

	// 接続が失敗したら呼び出されます
	function failConnect(e) {
	    console.log("connect failed");
	    console.log(e);
	}

	// 接続に成功したら呼び出されます
	function onConnect() {
	    console.log("onConnect");
			subscribe();
	}

	// メッセージが到着したら呼び出されるコールバック関数
	function onMessageArrived(message) {
	    console.log("onMessageArrived:"+message.payloadString);
      // データ追加
      myLineChart.addData([parseFloat(message.payloadString)], "");
      // 先頭データ削除
      myLineChart.removeData();
	}

	function subscribe(){
	    // コールバック関数を登録します
	    client.onMessageArrived = onMessageArrived;

	    var topic = "UedaTakeyuki@github/#";
	    // Subscribeします
	    client.subscribe(topic);
	}
	connect();
	//subscribe();
