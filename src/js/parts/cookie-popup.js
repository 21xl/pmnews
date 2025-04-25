document.addEventListener('DOMContentLoaded', function() {
    const cookiePopup = document.getElementById('cookie-popup');
    if (cookiePopup) {
        function checkCookiePopupStatus() {
            const accepted = localStorage.getItem('cookiePopupAccepted');
            const rejected = getCookie('cookie_cancel');

            if (!accepted && !rejected) {
                setTimeout(function() {
                    cookiePopup.style.display = 'block'; 
                    setTimeout(function() {
                        cookiePopup.style.opacity = 1;
                    }, 10);  
                }, 1200);  
            }
        }

        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days*24*60*60*1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }

        const acceptBtn = document.getElementById('accept-cookie-btn');
        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                localStorage.setItem('cookiePopupAccepted', 'true');
                cookiePopup.style.opacity = 0;  
                setTimeout(function() {
                    cookiePopup.style.display = 'none'; 
                }, 500);  
            });
        }
        const cancelBtn = document.getElementById('cancel-cookie-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                setCookie('cookie_cancel', 'true', 1);  
                cookiePopup.style.opacity = 0;
                setTimeout(function() {
                    cookiePopup.style.display = 'none';
                }, 500);
            });
        }

        checkCookiePopupStatus();
    }
});
