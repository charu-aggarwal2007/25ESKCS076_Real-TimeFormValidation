/**
 * Student Registration — Real-Time Validation
 * ------------------------------------------------------------
 * Validates every field AS the student types (on 'input' and
 * 'blur' events), gives instant visual + text feedback, tracks
 * overall completion, checks password strength live, and does
 * an AJAX duplicate-check against the database for email/phone
 * without a full page reload.
 */

document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('registrationForm');
    if (!form) return;

    const fields = {
        full_name: { el: document.getElementById('full_name'), valid: false },
        email:     { el: document.getElementById('email'),     valid: false },
        phone:     { el: document.getElementById('phone'),     valid: false },
        dob:       { el: document.getElementById('dob'),       valid: false },
        gender:    { el: null, valid: false }, // radio group, handled separately
        course:    { el: document.getElementById('course'),    valid: false },
        address:   { el: document.getElementById('address'),   valid: false },
        password:  { el: document.getElementById('password'),  valid: false },
        confirm_password: { el: document.getElementById('confirm_password'), valid: false }
    };

    const submitBtn = document.getElementById('submitBtn');
    const ledgerFill = document.getElementById('ledgerFill');
    const ledgerCount = document.getElementById('ledgerCount');
    const ledgerItems = document.querySelectorAll('.ledger-items li');

    let duplicateCheckTimers = {};

    // --------------------------------------------------------
    // Helpers to set field state (valid / invalid / neutral)
    // --------------------------------------------------------
    function setState(fieldWrapper, state, message) {
        fieldWrapper.classList.remove('valid', 'invalid');
        const hint = fieldWrapper.querySelector('.hint');
        if (state === 'valid') {
            fieldWrapper.classList.add('valid');
        } else if (state === 'invalid') {
            fieldWrapper.classList.add('invalid');
        }
        if (hint) hint.textContent = message || '';
    }

    function getWrapper(el) {
        return el.closest('.field');
    }

    // --------------------------------------------------------
    // Individual field validators
    // --------------------------------------------------------
    function validateName() {
        const el = fields.full_name.el;
        const val = el.value.trim();
        const wrapper = getWrapper(el);
        const re = /^[A-Za-z][A-Za-z\s.'-]{2,49}$/;

        if (val.length === 0) {
            setState(wrapper, 'neutral', '');
            fields.full_name.valid = false;
        } else if (!re.test(val)) {
            setState(wrapper, 'invalid', 'Use only letters, at least 3 characters.');
            fields.full_name.valid = false;
        } else {
            setState(wrapper, 'valid', 'Looks good.');
            fields.full_name.valid = true;
        }
        updateProgress();
    }

    function validateEmail() {
        const el = fields.email.el;
        const val = el.value.trim();
        const wrapper = getWrapper(el);
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

        if (val.length === 0) {
            setState(wrapper, 'neutral', '');
            fields.email.valid = false;
            updateProgress();
            return;
        }
        if (!re.test(val)) {
            setState(wrapper, 'invalid', 'Enter a valid email address.');
            fields.email.valid = false;
            updateProgress();
            return;
        }

        // Passed format check — now debounce a server duplicate check
        setState(wrapper, 'neutral', 'Checking availability…');
        fields.email.valid = false;
        clearTimeout(duplicateCheckTimers.email);
        duplicateCheckTimers.email = setTimeout(() => {
            checkDuplicate('email', val, wrapper, fields.email);
        }, 500);
    }

    function validatePhone() {
        const el = fields.phone.el;
        const val = el.value.trim();
        const wrapper = getWrapper(el);
        const re = /^[6-9]\d{9}$/; // Indian 10-digit mobile format

        if (val.length === 0) {
            setState(wrapper, 'neutral', '');
            fields.phone.valid = false;
            updateProgress();
            return;
        }
        if (!re.test(val)) {
            setState(wrapper, 'invalid', 'Enter a valid 10-digit mobile number.');
            fields.phone.valid = false;
            updateProgress();
            return;
        }

        setState(wrapper, 'neutral', 'Checking availability…');
        fields.phone.valid = false;
        clearTimeout(duplicateCheckTimers.phone);
        duplicateCheckTimers.phone = setTimeout(() => {
            checkDuplicate('phone', val, wrapper, fields.phone);
        }, 500);
    }

    function checkDuplicate(type, value, wrapper, fieldObj) {
        fetch('check_duplicate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `type=${encodeURIComponent(type)}&value=${encodeURIComponent(value)}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    setState(wrapper, 'invalid', `This ${type} is already registered.`);
                    fieldObj.valid = false;
                } else {
                    setState(wrapper, 'valid', 'Available.');
                    fieldObj.valid = true;
                }
                updateProgress();
            })
            .catch(() => {
                // If the server check fails (e.g. running without PHP yet),
                // don't block the student — fall back to format validity.
                setState(wrapper, 'valid', 'Format looks good.');
                fieldObj.valid = true;
                updateProgress();
            });
    }

    function validateDob() {
        const el = fields.dob.el;
        const val = el.value;
        const wrapper = getWrapper(el);

        if (!val) {
            setState(wrapper, 'neutral', '');
            fields.dob.valid = false;
            updateProgress();
            return;
        }

        const dob = new Date(val);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;

        if (dob > today) {
            setState(wrapper, 'invalid', 'Date of birth cannot be in the future.');
            fields.dob.valid = false;
        } else if (age < 15) {
            setState(wrapper, 'invalid', 'You must be at least 15 years old.');
            fields.dob.valid = false;
        } else if (age > 100) {
            setState(wrapper, 'invalid', 'Please check the date entered.');
            fields.dob.valid = false;
        } else {
            setState(wrapper, 'valid', `Age: ${age} years.`);
            fields.dob.valid = true;
        }
        updateProgress();
    }

    function validateGender() {
        const checked = form.querySelector('input[name="gender"]:checked');
        fields.gender.valid = !!checked;
        updateProgress();
    }

    function validateCourse() {
        const el = fields.course.el;
        const wrapper = getWrapper(el);
        if (!el.value) {
            setState(wrapper, 'neutral', '');
            fields.course.valid = false;
        } else {
            setState(wrapper, 'valid', '');
            fields.course.valid = true;
        }
        updateProgress();
    }

    function validateAddress() {
        const el = fields.address.el;
        const val = el.value.trim();
        const wrapper = getWrapper(el);

        if (val.length === 0) {
            setState(wrapper, 'neutral', '');
            fields.address.valid = false;
        } else if (val.length < 10) {
            setState(wrapper, 'invalid', 'Please enter your full address (min 10 characters).');
            fields.address.valid = false;
        } else {
            setState(wrapper, 'valid', 'Looks good.');
            fields.address.valid = true;
        }
        updateProgress();
    }

    // Password strength: length, upper/lowercase, number, special char
    function validatePassword(showHint = true) {
        const el = fields.password.el;
        const val = el.value;
        const wrapper = getWrapper(el);
        const bars = document.querySelectorAll('.strength-meter i');

        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const colors = ['#c1473f', '#c1473f', '#c8963e', '#2f8f5b'];
        const labels = ['Too weak', 'Weak', 'Good', 'Strong'];

        bars.forEach((bar, i) => {
            bar.style.background = (val.length > 0 && i < score) ? colors[score - 1] : '';
        });

        if (val.length === 0) {
            setState(wrapper, 'neutral', 'Min 8 characters, with a number and a symbol.');
            fields.password.valid = false;
        } else if (score < 3) {
            setState(wrapper, 'invalid', labels[score - 1] || 'Too weak — add numbers, symbols, and mixed case.');
            fields.password.valid = false;
        } else {
            setState(wrapper, 'valid', labels[score - 1]);
            fields.password.valid = true;
        }

        // Re-check confirm password whenever password changes
        if (fields.confirm_password.el.value.length > 0) validateConfirmPassword();
        updateProgress();
    }

    function validateConfirmPassword() {
        const el = fields.confirm_password.el;
        const wrapper = getWrapper(el);
        const val = el.value;

        if (val.length === 0) {
            setState(wrapper, 'neutral', '');
            fields.confirm_password.valid = false;
        } else if (val !== fields.password.el.value) {
            setState(wrapper, 'invalid', 'Passwords do not match.');
            fields.confirm_password.valid = false;
        } else {
            setState(wrapper, 'valid', 'Passwords match.');
            fields.confirm_password.valid = true;
        }
        updateProgress();
    }

    // --------------------------------------------------------
    // Progress ledger (the sidebar checklist + progress bar)
    // --------------------------------------------------------
    function updateProgress() {
        const keys = Object.keys(fields);
        const total = keys.length;
        const done = keys.filter(k => fields[k].valid).length;
        const pct = Math.round((done / total) * 100);

        if (ledgerFill) ledgerFill.style.width = pct + '%';
        if (ledgerCount) ledgerCount.textContent = pct + '%';

        // Map ledger checklist items to logical groups
        const groups = {
            'li-identity': fields.full_name.valid && fields.dob.valid && fields.gender.valid,
            'li-contact': fields.email.valid && fields.phone.valid,
            'li-academic': fields.course.valid && fields.address.valid,
            'li-security': fields.password.valid && fields.confirm_password.valid
        };
        ledgerItems.forEach(li => {
            const key = li.dataset.key;
            if (groups[key]) li.classList.add('done');
            else li.classList.remove('done');
        });

        // Enable submit only when everything is valid
        const allValid = keys.every(k => fields[k].valid);
        if (submitBtn) submitBtn.disabled = !allValid;
    }

    // --------------------------------------------------------
    // Wire up events
    // --------------------------------------------------------
    fields.full_name.el.addEventListener('input', validateName);
    fields.email.el.addEventListener('input', validateEmail);
    fields.phone.el.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        validatePhone();
    });
    fields.dob.el.addEventListener('input', validateDob);
    fields.dob.el.addEventListener('change', validateDob);
    form.querySelectorAll('input[name="gender"]').forEach(r => r.addEventListener('change', validateGender));
    fields.course.el.addEventListener('change', validateCourse);
    fields.address.el.addEventListener('input', validateAddress);
    fields.password.el.addEventListener('input', () => validatePassword(true));
    fields.confirm_password.el.addEventListener('input', validateConfirmPassword);

    // Initialize progress on load
    updateProgress();

    // --------------------------------------------------------
    // Final submit guard (defense in depth — server re-validates too)
    // --------------------------------------------------------
    form.addEventListener('submit', (e) => {
        const allValid = Object.keys(fields).every(k => fields[k].valid);
        if (!allValid) {
            e.preventDefault();
            return;
        }
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });
});
