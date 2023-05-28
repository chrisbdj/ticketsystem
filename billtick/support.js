function addUserTickets() {
    $.get("api.php?act=get", function (data) {
        var newticks = "";
        var pendingticks ="";
        var obj = JSON.parse(data);
        for (let i=0; i<obj.length; i++) {
            if (obj[i].status == "new") {
                newticks += "<a href="+makeClickable(obj[i].hash)+"><div class='general-table'>";
                newticks += "<div class='subject'>"+obj[i].subject+"</div>";
                newticks += "<div class='item'><span class='showPHONE'>Date Created: </span>" +obj[i].dateCreated.substr(0,10) + "</div>";
                newticks += "<div class='item'><span class='showPHONE '>Date Updated: </span>"+ obj[i].lastUpdated.substr(0,10) +"</div>";
                newticks += "</div></a>";
            } else if (obj[i].status == "pending") {
                pendingticks += "<a href="+makeClickable(obj[i].hash)+"><div class='general-table'>";
                pendingticks += "<div class='subject'>"+obj[i].subject+"</div>";
                pendingticks += "<div class='item'><span class='showPHONE'>Date Created: </span>" +obj[i].dateCreated.substr(0,10) + "</div>";
                pendingticks += "<div class='item'><span class='showPHONE '>Date Updated: </span>"+ obj[i].lastUpdated.substr(0,10) +"</div>";
                pendingticks += "</div></a>";
            }
        }
        var newtickets = document.querySelector("#opentickets");
        newtickets.insertAdjacentHTML("afterend", newticks);
        var pendingtickets = document.querySelector("#pendingtickets");
        pendingtickets.insertAdjacentHTML("afterend", pendingticks);
    });
}
function makeClickable(str) {
    str = "openticket.html?ticket="+str;
    return str;
}


$(document).ready(function(){
    addUserTickets();
});


/*
<div class="general-table">
        <div class="subject">Add Image to Home Page</div>
        <div class="item"><span class="showPHONE">Date Created: </span>3/24/2021</div>
        <div class="item"><span class="showPHONE ">Date Updated: </span>3/23/2021</div>
      </div>
      <div class="general-table">
        <div class="subject">Add Image to Home Page</div>
        <div class="item"><span class="showPHONE">Date Created: </span>3/24/2021</div>
        <div class="item"><span class="showPHONE">Date Updated: </span>3/23/2021</div>
      </div>
      <div class="general-table">
        <div class="subject">Add Image to Home Page</div>
        <div class="item"><span class="showPHONE">Date Created: </span>3/24/2021</div>
        <div class="item"><span class="showPHONE">Date Updated: </span>3/23/2021</div>
      </div>
*/