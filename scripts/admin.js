document.addEventListener('DOMContentLoaded', function(){
    const login_btn = document.getElementById('login');
    let cont = document.querySelector('.parent-container');
    const login_container = document.querySelector('.login-container');
    const input_cont = document.querySelector('.input-container');
    const create_con = document.getElementById('createcon');
    const payment_con = document.getElementById('paymentcon');

    login_btn.addEventListener('click', function(e){
        const uname = document.getElementById('admin-user').value.trim();
        const pass = document.getElementById('admin-pass').value.trim();    
        e.preventDefault();

        if(uname == "Admin123" && pass == 'Admin14356'){
            if (login_container) login_container.style.display = "none";
            if (cont) cont.style.display = "flex";
            if (input_cont) input_cont.style.display = "flex";
            if (create_con) create_con.style.display = "flex";
            if (payment_con) payment_con.style.display = "flex";

         } else {
            alert("Invalid admin credentials!" + pass + ' ' + uname + ' ');
        }
    });

});