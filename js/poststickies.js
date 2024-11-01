function poststickyMakeShortcode() {
    var color = document.getElementById("poststicky-color").value;
    var font = document.getElementById("poststicky-font").value;
    var alignRight = document.getElementById("poststicky-align-right");
    var alignLeft = document.getElementById("poststicky-align-left");
    // var header = document.getElementById("poststicky-header").value;
    var body = document.getElementById("poststicky-body").value;
    var footer = document.getElementById("poststicky-footer").value;

    //var newShortCode=document.getElementById("postpanel-shortcode").value;

    myShortcode = '[post-sticky type="stickynote" color="' + color
        + '" font="' + font + '" align="' + alignment(alignLeft, alignRight)
        + '" ';
    /*
    if (!empty(header)) {
      myShortcode = myShortcode + ' header="' + header + '"';
  } */

    if (!empty(body)) {
      myShortcode = myShortcode + ' body="' + body + '"';
    }

    if (!empty(footer)) {
      myShortcode = myShortcode + ' footer="' + footer + '"';
    }

    myShortcode = myShortcode + ']';

    //newShortCode.innerHTML = myShortcode;
    document.getElementById("poststicky-mycode").value = myShortcode;

    //window.alert(myShortcode);

    color = 'undefined';
    font = 'undefined';
    // header = 'undefined';
    body = 'undefined';
    footer = 'undefined';
}

/* Thanks to https://www.sitepoint.com/testing-for-empty-values/ for the code! */
function empty(data)
{
  if(typeof(data) == 'number' || typeof(data) == 'boolean')
  {
    return false;
  }
  if(typeof(data) == 'undefined' || data === null)
  {
    return true;
  }
  if(typeof(data.length) != 'undefined')
  {
    return data.length == 0;
  }
  var count = 0;
  for(var i in data)
  {
    if(data.hasOwnProperty(i))
    {
      count ++;
    }
  }
  return count == 0;
}
function alignment(left, right) {
    if (left.checked) {
        return "left";
    } else {
        return "right";
    }
}
