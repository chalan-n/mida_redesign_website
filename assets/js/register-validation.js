document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('body.register-page form');

    if (!form) {
        return;
    }

    form.setAttribute('novalidate', 'novalidate');

    const submitBtn = document.getElementById('submitBtn');
    const alert = document.createElement('div');
    let hasAttemptedSubmit = false;

    alert.className = 'register-form-alert';
    alert.id = 'registerFormAlert';
    alert.hidden = true;
    alert.setAttribute('role', 'alert');
    alert.innerHTML = '<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>กรุณากรอกข้อมูลที่จำเป็นให้ครบก่อนส่งข้อมูล</span>';
    form.insertBefore(alert, form.firstElementChild);

    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }

    function getWrapper(input) {
        return input.closest('.form-group') || input.closest('.register-privacy-note') || input.parentElement;
    }

    function getMessage(input) {
        if (input.type === 'checkbox') {
            return 'กรุณายอมรับเงื่อนไขและนโยบายความเป็นส่วนตัว';
        }

        const wrapper = getWrapper(input);
        const label = wrapper ? wrapper.querySelector('.form-label') : null;
        const labelText = label ? label.textContent.replace('*', '').trim() : '';

        if (input.tagName === 'SELECT') {
            return labelText ? 'กรุณาเลือก' + labelText : 'กรุณาเลือกรายการนี้';
        }

        return labelText ? 'กรุณาระบุ' + labelText : 'กรุณากรอกข้อมูลช่องนี้';
    }

    function getErrorElement(input) {
        const wrapper = getWrapper(input);

        if (!wrapper) {
            return null;
        }

        let error = wrapper.querySelector('.register-field-error');

        if (!error) {
            error = document.createElement('small');
            error.className = 'register-field-error';
            error.id = (input.id || input.name || 'registerField') + 'Error';
            wrapper.appendChild(error);
        }

        return error;
    }

    function setFieldError(input, message) {
        const wrapper = getWrapper(input);
        const error = getErrorElement(input);

        if (!wrapper || !error) {
            return;
        }

        wrapper.classList.toggle('is-invalid', Boolean(message));
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        error.textContent = message || '';

        if (message) {
            input.setAttribute('aria-describedby', error.id);
        } else {
            input.removeAttribute('aria-describedby');
        }
    }

    function isInvalid(input) {
        if (input.disabled || input.type === 'hidden') {
            return false;
        }

        if (input.type === 'checkbox') {
            return input.required && !input.checked;
        }

        return input.required && input.value.trim() === '';
    }

    function validate(showErrors) {
        const fields = Array.from(form.querySelectorAll('input[required], select[required], textarea[required]'));
        let firstInvalid = null;

        fields.forEach(function (input) {
            const message = isInvalid(input) ? getMessage(input) : '';
            setFieldError(input, showErrors ? message : '');

            if (message && !firstInvalid) {
                firstInvalid = input;
            }
        });

        alert.hidden = !(showErrors && firstInvalid);
        return firstInvalid;
    }

    form.addEventListener('submit', function (event) {
        hasAttemptedSubmit = true;

        const firstInvalid = validate(true);

        if (!firstInvalid) {
            return;
        }

        event.preventDefault();
        firstInvalid.focus({ preventScroll: true });
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    form.addEventListener('input', function (event) {
        if (!hasAttemptedSubmit || !event.target.matches('input, select, textarea')) {
            return;
        }

        validate(true);
    });

    form.addEventListener('change', function (event) {
        if (!hasAttemptedSubmit || !event.target.matches('input, select, textarea')) {
            return;
        }

        validate(true);
    });
});
