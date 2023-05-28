
var toggleEdit = false;
var editDetailsAnchor = document.getElementById("editBtn");
var lockIcons = document.querySelectorAll("i.fa-lock");

function toggleEditDetails() {
    if (!toggleEdit) {
        editDetailsAnchor.innerText = "finish?";
        toggleEdit = true;
        toggleLocks(toggleEdit);
    } else {
        editDetailsAnchor.innerHTML = "edit<span class='hideTAB'>&nbsp;details</span>?";
        
        sendUpdateData();
        toggleEdit = false;
        toggleLocks(toggleEdit);
    }
}
function toggleLocks(b) {
    let input = document.querySelectorAll(".input-container input[type='text'], .input-container select");
    if (b) {
        for (var i = 0; i < lockIcons.length; i++) {
            lockIcons[i].style.opacity = 0;
            input[i].disabled = false;
        }
    } else {
        for (var i = 0; i < lockIcons.length; i++) {
            lockIcons[i].style.opacity = 1;
            input[i].disabled = true;
        }
    }
}

function addUserDomains(domains) {
    console.log("add domains");
    if (domains != undefined) {
        let str = "<div class='spacer margin3 hideALL'></div><div id='domains' class='content-block'><h4>My Websites</h4>";
        for (var i = 0; i < domains.length; i++) {
            str += "<div class='general-color-container'><div class='subject text-center'><p>" + domains[i] + "</p></div></div>";
        }
        str += "<div class='spacer margin2'></div></div>";
        var main = document.querySelector("#aboutme");
        main.insertAdjacentHTML("afterend", str);
    }

}
function addHelpBlock() {
    let str = "<div class='content-block'><div class='general-container flex flex-row align-center justify-center text-center'><div class='support-icon'><svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 37 42'><path fill='#2E2E2E' d='M18.5 0C8.292 0 0 8.001 0 17.85 0 27.699 8.292 35.7 18.5 35.7h1.088V42C30.166 37.086 37 27.3 37 17.85 37 8.001 28.708 0 18.5 0zm-.044 29.4c-1.284 0-2.285-.987-2.285-2.205 0-1.239 1.023-2.184 2.285-2.184 1.285 0 2.264.945 2.264 2.184 0 1.218-.958 2.205-2.264 2.205zm5.463-12.957c-1.37 1.953-2.677 2.541-3.395 3.801-.174.294-.283.546-.348 1.029a1.628 1.628 0 01-1.632 1.428h-.066c-.957 0-1.72-.798-1.632-1.722.065-.588.196-1.197.544-1.764.892-1.533 2.568-2.436 3.548-3.78 1.044-1.428.457-4.074-2.482-4.074-1.327 0-2.198.672-2.742 1.47-.413.609-1.24.819-1.937.525-.914-.378-1.306-1.47-.74-2.247 1.11-1.554 2.96-2.709 5.398-2.709 2.677 0 4.527 1.176 5.463 2.646.783 1.281 1.262 3.633.021 5.397z'/></svg></div><p class='padding1'>Would you like to make changes to your website?<br> Ask Us A Question!</p></div></div>";
    var main = document.querySelector("main");
    main.insertAdjacentHTML("beforeend", str);
}

function populateUserInfo() {
    let user = getUrlParam('id', '');
    if (user != '') {
        user = "&id="+user;
    }
    $.get("api.php?act=info"+user, function (data) {
        let obj = JSON.parse(data);
        let userid = document.getElementById("id");
        id.value=obj.id;
        let name = document.getElementById("name");
        name.value=obj.name;
        let email = document.getElementById("email");
        email.value=obj.email;
        let address = document.getElementById("address");
        address.value=obj.address;
        let city = document.getElementById("city");
        city.value=obj.city;
        let province = document.getElementById("province");
        province.option=obj.province;
        let postal = document.getElementById("postal");
        postal.value=obj.postal;
        let phone = document.getElementById("phone");
        phone.value=obj.phone;
        let domains = document.getElementById("domains");
        domains.value=obj.domains;
    });
}

function sendUpdateData() {
    console.log("Update Profile FORM SUBMIT");
    let formValues = $("#profiledata").serialize();
    sendIt(formValues);
}



$( document ).ready(function() {
    populateUserInfo();
    $("#editBtn").click(function(event) {
        event.preventDefault();
        toggleEditDetails();
    });
});



