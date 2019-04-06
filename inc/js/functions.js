function moveNumbersDown(id){
  var e1, e2;
  for(var i = 10; i > id; i--){
  	e1 = document.getElementById(i.toString());
  	e2 = document.getElementById((i-1).toString());
  	if ((e1 != null)&&(e2 != null)&&((e1.value == '0')||(e1.value==''))) {
  		e1.value = e2.value;
      e2.value = '';
  	}
  }
  if (id < 1)
    e2 = document.getElementById('exp');
  else
    e2 = document.getElementById(id.toString());
  if (e2 != null)
  	e2.value = '';
}
