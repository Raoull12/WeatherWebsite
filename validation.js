// getting all the elements includding the form and putting them in seperate variables.
const username = document.getElementById('username');
const email = document.getElementById('email');
const password = document.getElementById('password');
const password_confirmation = document.getElementById('password_confirmation');
const form = document.getElementById('form');
const errorElement = document.getElementById('error');

form.addEventListener('submit', (e) => { // when clicking submit the following will be checked
    let messages = [] // creating an array to store the error messages

    if(password.value.length <= 7 ) 
    {
        messages.push('Password must have at least 8 characters');
    }

    if(password.value.length >= 25) 
    {
        messages.push('Password cannot have more than 24 characters');
    }

    //Checking for at least one number
    if (!/\d/.test(password.value)) 
    {
        messages.push('Password must contain at least one number');
    }
    
    // Checking for at least one symbol
    if (!/[!@#$%^&*]/.test(password.value)) 
    {
        messages.push('Password must contain at least one symbol');
    }

    if(password.value !== password_confirmation.value)
    {
        messages.push('Passwords have to match');
    }

    if(messages.length > 0) {
        e.preventDefault(); // prevent redirect to registration.php if messages array is populated
        errorElement.innerText = messages.join(', '); // joining multiple errors with a , for a better user experience.
    }
});