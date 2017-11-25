/** 
 * Make Vue body for movie alart vue and return it. 
 * @pamam {string} el_str            name of el.
 * @pamam {string} serial_id_str     serial_id.
 */
function alart_vue_body(el_str, serial_id_str){
  return  {
    el: el_str,
    data: {
      message: '2016年9月7日、水警報受信',
    },
    methods: {
      release: function(){
        // 表示を消す
        $(el_str).removeClass("hidden");
//          alart_vue.message = data.water;
        this.message = "";
        // alart.ini を消す
        $.ajax({
          type: "POST",
          url: "postalart.php",
          data: {
            serial_id: serial_id_str,
            name: "water",
            status: "off"
          },
          dataType: "json",
        })
        .then(
          function(data, dataType){
          },
          function(XMLHttpRequest, textStatus, errorThrown){
            console.log('Error : ' + errorThrown);
        });
      },
      check_alart: function(){ 
        setInterval(function(){
          $.ajax({
            type: "POST",
            url: "alart.php",
            data: {serial_id: serial_id_str},
            dataType: "json",
          })
          .then(
            function(data, dataType){
              if (data.water != ""){
                $(el_str).removeClass("hidden");
                alart_vue.message = data.water;
              } else {
                $(el_str).addClass("hidden");
                alart_vue.message = "";
              }
            },
            function(XMLHttpRequest, textStatus, errorThrown){
              console.log('Error : ' + errorThrown);
          })
        }, 1000 );
      }, 
    }
  };
}

