function sendIt(data) {
    //data = JSON.stringify(data);
    $.ajax({
        url: "api.php",
        type: "POST",
        dataType: "json",
        data: data,
        success: function (data) {
            //var obj = JSON.parse(data);
            console.log(data.result);
            if (data.result) {
                window.location.href = "index.html";
            }
        },
        error: function(log) {
            // handle error
            let obj = JSON.parse(log);
            console.log("THIS IS AN ERROR: "+obj);
        }
    });

}
$(document).ready(function(){
    $.get("api.php", function (data) {
        let obj = JSON.parse(data);
        if (obj.authenticated) {
            window.location.href = "index.html";
        }
    });
	$("#login").submit(function(event){
        console.log("LOG IN FORM SUBMIT");
        // Stop form from submitting normally
        event.preventDefault();
        /* Serialize the submitted form control values to be sent to the web server with the request */
        let formValues = $(this).serialize();
        // Send the form data using post
        sendIt(formValues);
    });
});