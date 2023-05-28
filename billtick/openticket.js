

function addPostsToTicket(id) {
    id = "&ticket="+id;
    $.get("api.php?act=display"+id, function (data) {
        var posts = "";
        var pendingticks ="";
        var obj = JSON.parse(data);
        for (let i=0; i<obj.length; i++) {
            temp = posts;
            posts = "<div class='general-color-container'><div class='message-table'>";
            posts += "<div class='poster-col'><p><span class='poster'>"+obj[i].author+"</span></p></div>";
            posts += "<div class='date-col'><p><span class='date'>" +obj[i].date.substr(0,10) + "</span></p></div>";
            posts += "<div class='message-col'><p><span class='content'>"+ obj[i].message +"</span></p></div>";
            posts += "</div></div><div class='spacer'></div>";
            posts = posts+temp;
        }
        var postContent = document.querySelector("#responder");
        postContent.insertAdjacentHTML("afterend", posts);
    });
}
/*<div class="general-color-container">
			<div class="message-table">
				  <div class="poster-col">
					<p><span class="poster">Chris</span>:</p>
				  </div>
				  <div class="date-col">
					  <p><span class="date">3/24/2021</span></p>
				  </div>
				  <div class="message-col">
					<p>
						<span class="content">I have made changes to the page as you requested, let me know if you need anything else!</span>
				   </p>
				  </div>
			</div>
		</div>
		<div class="spacer"></div>*/


$(document).ready(function(){

    var ticketid = getUrlParam('ticket', '');
    if (ticketid == undefined) {
        console.log("NO TICKET ID");
        
        var subjecttitle = document.querySelector("#ticketsubject");
        subjecttitle.style.display = "none";
        
    } else {
        console.log("TICKET ID: "+ticketid);

        var subjecttitle = document.querySelectorAll(".jshide");
        for (let i=0; i<subjecttitle.length; i++){
            subjecttitle[i].style.display = "none";
        }

        var ticket = document.getElementById("ticket");
        ticket.value = ticketid;
        
        addPostsToTicket(ticketid);
    }
   

    $("#submitticket").submit(function(event){
        console.log("CREATE TICKET FORM SUBMIT");
        event.preventDefault();
        let formValues = $(this).serialize();
        sendIt(formValues);       
    });
});