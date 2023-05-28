function addTicket(item) {
    
    console.log(obj.authour);
    console.log(obj.subject);
}
function addUserTickets() {
    $.get("api.php?act=get", function (data) {
        var str="";
        var obj = JSON.parse(data);
        for (let i=0; i<obj.length; i++) {
            str += "<h4>"+obj[i].subject+"</h4>";
            str += "<p>" +obj[i].author + "</p>";
            str += "<br>";
            console.log(str);
        }
        var tickets = document.querySelector("#tickets");
        tickets.insertAdjacentHTML("beforeend", str);
    });
}


/*ABOVE THIS IS PAGE SPECIFIC STUFF*/
function checkAuth() {
    $.get("api.php", function (data) {
        let obj = JSON.parse(data);
        if (!obj.authenticated) {
            window.location.href = "login.html";
        }
    });
}
function sendIt(data) {
    //data = JSON.stringify(data);
    console.log("HEY IM TRYNNA SEND");
    $.ajax({
        url: "api.php",
        type: "POST",
        dataType: "json",
        data: data,
        success: function (result) {
            let obj = JSON.parse(result);
            console.log(obj);
        },
        error: function(log) {
            // handle error
            let obj = JSON.parse(log);
            console.log("THIS IS AN ERROR: "+obj);
        }
    });

}

function getUrlVars() {
    let vars = {};
    let parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
        vars[key] = value;
    });
    return vars;
}
function getUrlParam(parameter, defaultvalue) {
    let urlparameter = defaultvalue;
    if (window.location.href.indexOf(parameter) > -1) {
        urlparameter = getUrlVars()[parameter];
    }
    return urlparameter;
}
function splitData(str) {
    if (str.slice(-1) == ",") {
        str = str.slice(0, -1);
    }
    let res = str.split(",");

    return res;
}

$(document).ready(function() {

    /*BELOW THIS CAN BE DELETED*/
  


    /*ABOVE THIS CAN BE DELETED*/


    checkAuth();

    //addUserTickets();

    
    $("#create").submit(function(event){
        console.log("CREATE FORM SUBMIT");
        event.preventDefault();
        let formValues = $(this).serialize();
        sendIt(formValues);
        
    });
    $("#createticket").submit(function(event){
        console.log("CREATE TICKET FORM SUBMIT");
        event.preventDefault();
        let formValues = $(this).serialize();
        sendIt(formValues);
        
    });

});




/*
    $.ajax({
        type: 'POST',
        url: 'api.php?act=change',
        data: data,
        success: function (data) {
            console.log(data);
        }
    });

    $.get("api.php?act=check", function (data) {
        var obj = JSON.parse(data);
        console.log(obj.success)
         if (obj.success) {
             setMe(obj.id);    
        } else {
            window.location.href = "login.html";
        }
    });*/
