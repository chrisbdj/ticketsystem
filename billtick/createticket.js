$(document).ready(function(){
    $("#createticket").submit(function(event){
        console.log("CREATE TICKET FORM SUBMIT");
        event.preventDefault();
        let formValues = $(this).serialize();
        sendIt(formValues);       
    });
});