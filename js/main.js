/* ======================================
   Guitar Shop - Main JavaScript
====================================== */

document.addEventListener("DOMContentLoaded", function() {

    initLogoutConfirm();
    initLiveSearch();
    initAutoCloseSearch();
    initScrollNavbar();

});


/* ======================================
   1. Confirm Logout
====================================== */
function initLogoutConfirm() {

    const logoutLinks = document.querySelectorAll('a[href="logout.php"]');

    logoutLinks.forEach(link => {
        link.addEventListener("click", function(e){
            if(!confirm("คุณต้องการออกจากระบบหรือไม่?")){
                e.preventDefault();
            }
        });
    });

}


/* ======================================
   2. Live Search Suggest
====================================== */
function initLiveSearch(){

    const searchInput = document.getElementById("searchInput");
    const resultBox  = document.getElementById("searchResult");

    if(!searchInput || !resultBox) return;

    let timeout = null;

    searchInput.addEventListener("keyup", function(){

        clearTimeout(timeout);

        let keyword = this.value.trim();

        if(keyword.length < 2){
            resultBox.style.display = "none";
            resultBox.innerHTML = "";
            return;
        }

        timeout = setTimeout(() => {

            fetch("search_suggest.php?keyword=" + encodeURIComponent(keyword))
            .then(res => res.text())
            .then(data => {

                if(data.trim() !== ""){
                    resultBox.innerHTML = data;
                    resultBox.style.display = "block";
                }else{
                    resultBox.style.display = "none";
                }

            })
            .catch(err => {
                console.error("Search error:", err);
            });

        }, 300);

    });

}


/* ======================================
   3. Auto Close Search Dropdown
====================================== */
function initAutoCloseSearch(){

    const resultBox = document.getElementById("searchResult");
    const searchInput = document.getElementById("searchInput");

    if(!resultBox || !searchInput) return;

    document.addEventListener("click", function(e){

        if(
            !searchInput.contains(e.target) &&
            !resultBox.contains(e.target)
        ){
            resultBox.style.display = "none";
        }

    });

}


/* ======================================
   4. Navbar Scroll Effect
====================================== */
function initScrollNavbar(){

    const navbar = document.querySelector(".navbar");

    if(!navbar) return;

    window.addEventListener("scroll", function(){

        if(window.scrollY > 20){
            navbar.classList.add("shadow");
        } else {
            navbar.classList.remove("shadow");
        }

    });

}
