window.formStateForgot = {
    password: false,
    confirm: false
};

window.checkFormValidity = function() {
    const btn = document.getElementById('submit-btn');

    if (formStateForgot.password && formStateForgot.confirm) {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    } else {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    }
};