// RIYMON ARONG
// DATE PROJECT DONE 08/06/25 THIS IS THE BETTER VERSION OF MY MCWD PROJECT THE FIRST VERSION IS
// C CONSOLE PROGRAMMING LANGUAGE
document.addEventListener('DOMContentLoaded', function(){
    const login_btn = document.getElementById('login');
    let cont = document.querySelector('.parent-container');
    const login_container = document.querySelector('.login-container');
    const input_cont = document.querySelector('.input-container');
    const create_con = document.getElementById('createcon');
    const payment_con = document.getElementById('paymentcon');
    const search_con = document.getElementById('searchcon');
    const header = document.querySelector('.header-text');

    login_btn.addEventListener('click', function(e){
        const uname = document.getElementById('admin-user').value.trim();
        const pass = document.getElementById('admin-pass').value.trim();    
        e.preventDefault();

        if(uname == "Admin123" && pass == 'Admin14356'){
            if (login_container) login_container.style.display = "none";
            alert("Login Succesfull!")
            if (cont) cont.style.display = "flex";
            if (input_cont) input_cont.style.display = "flex";
            if (create_con) create_con.style.display = "flex";
            if (payment_con) payment_con.style.display = "flex";
            if (search_con) search_con.style.display = "block";

            const logout = document.createElement('a');
            logout.className = 'button';
            logout.textContent = 'Logout';
            logout.href = "index.html"
            logout.style.cursor = 'pointer';
            logout.style.textDecoration = 'none';
            logout.style.padding = '.5vh 2vh';
            logout.style.color = 'white';
            logout.style.border = 'none';
            logout.style.borderRadius = '4px';
            logout.style.position = 'absolute';
            logout.style.right = '3vh';
            logout.style.top = '0vh';

            if (header) header.appendChild(logout);
            
            logout.addEventListener('click', function() {
                header.removeChild(logout);
                document.getElementById('admin-user').value = '';
                document.getElementById('admin-pass').value = '';
            });
        } else if(uname == 'Admin123' && pass != 'Admin14356'){
            alert("Password Incorrect!")
        } else if(uname != 'Admin123' && pass == 'Admin14356'){
            alert("Username Incorrect!")
         } else {
            alert("Invalid admin credentials!");
        }
    });
});