// generated.js — Onboarding flow logic with Steps.js, transitions, and validation.

// --- Stepper setup ---

// 1. Steps.js — create the steps navigation.
const steps = [
    { label: 'Register' },
    { label: 'Admin User' },
    { label: 'Preferences' },
    { label: 'Done' }
  ];
  
  const stepsInst = new Steps({
    el: "#onboarding-steps",
    steps: steps.map(s => s.label),
    current: 0
  });
  
  let currentStep = 0;
  const totalSteps = steps.length;
  
  // 2. Get elements
  const stepEls = document.querySelectorAll('.onboarding-step');
  const form = document.getElementById('onboarding-form');
  const nextBtn = document.getElementById('next-btn');
  const prevBtn = document.getElementById('prev-btn');
  const navDiv = document.getElementById('onboarding-nav');
  const doneBtn = document.getElementById('onboarding-done');
  
  // Utility: show/hide steps with fade transitions
  function showStep(stepIdx, animate = true) {
    stepEls.forEach((el, i) => {
      if (i === stepIdx) {
        el.classList.remove('hidden');
        if (animate) {
          el.classList.add('animate-fade-in');
          el.classList.remove('animate-fade-out');
        }
      } else {
        if (!el.classList.contains('hidden')) {
          if (animate) {
            el.classList.remove('animate-fade-in');
            el.classList.add('animate-fade-out');
            setTimeout(() => el.classList.add('hidden'), 300);
          } else {
            el.classList.add('hidden');
          }
        }
      }
    });
    stepsInst.go(stepIdx);
  }
  
  function setButtons() {
    if (currentStep === 0) {
      prevBtn.disabled = true;
      nextBtn.style.display = '';
      nextBtn.innerText = "Next";
      navDiv.style.display = '';
    } else if (currentStep === totalSteps - 1) {
      // Confirmation: hide nav, show Done
      navDiv.style.display = 'none';
    } else {
      prevBtn.disabled = false;
      nextBtn.style.display = '';
      nextBtn.innerText = currentStep === totalSteps - 2 ? "Finish" : "Next";
      navDiv.style.display = '';
    }
  }
  
  // --- Validation functions ---
  const validators = [
    // Step 1
    function step1() {
      let valid = true;
      // Company name
      const companyInput = document.getElementById('company_name');
      const errName = companyInput.nextElementSibling;
      if (!companyInput.value.trim()) {
        errName.textContent = 'Company name is required.';
        errName.classList.remove('hidden');
        valid = false;
      } else {
        errName.textContent = '';
        errName.classList.add('hidden');
      }
      // Subdomain
      const sdInput = document.getElementById('subdomain');
      const errSd = sdInput.parentElement.nextElementSibling;
      let val = sdInput.value.trim();
      if (!val) {
        errSd.textContent = 'Subdomain is required.';
        errSd.classList.remove('hidden');
        valid = false;
      } else if (!/^[a-zA-Z0-9-]{3,20}$/.test(val)) {
        errSd.textContent = '3-20 chars, letters, numbers or dashes only.';
        errSd.classList.remove('hidden');
        valid = false;
      } else {
        errSd.textContent = '';
        errSd.classList.add('hidden');
      }
      return valid;
    },
    // Step 2
    function step2() {
      let valid = true;
      const name = document.getElementById('admin_name');
      const email = document.getElementById('admin_email');
      const pass = document.getElementById('admin_password');
      const errName = name.nextElementSibling;
      const errEmail = email.nextElementSibling;
      const errPass = pass.nextElementSibling;
  
      if (!name.value.trim()) {
        errName.textContent = 'Name is required.';
        errName.classList.remove('hidden');
        valid = false;
      } else {
        errName.textContent = '';
        errName.classList.add('hidden');
      }
      if (!email.value.trim()) {
        errEmail.textContent = 'Email is required.';
        errEmail.classList.remove('hidden');
        valid = false;
      } else if (!/^\S+@\S+\.\S+$/.test(email.value)) {
        errEmail.textContent = 'Enter a valid email address.';
        errEmail.classList.remove('hidden');
        valid = false;
      } else {
        errEmail.textContent = '';
        errEmail.classList.add('hidden');
      }
      if (!pass.value) {
        errPass.textContent = 'Password is required.';
        errPass.classList.remove('hidden');
        valid = false;
      } else if (pass.value.length < 6) {
        errPass.textContent = 'At least 6 characters.';
        errPass.classList.remove('hidden');
        valid = false;
      } else {
        errPass.textContent = '';
        errPass.classList.add('hidden');
      }
      return valid;
    },
    // Step 3
    function step3() {
      let valid = true;
      const tz = document.getElementById('timezone');
      const lang = document.getElementById('language');
      const errTz = tz.nextElementSibling;
      const errLang = lang.nextElementSibling;
      if (!tz.value) {
        errTz.textContent = 'Timezone is required.';
        errTz.classList.remove('hidden');
        valid = false;
      } else {
        errTz.textContent = '';
        errTz.classList.add('hidden');
      }
      if (!lang.value) {
        errLang.textContent = 'Language is required.';
        errLang.classList.remove('hidden');
        valid = false;
      } else {
        errLang.textContent = '';
        errLang.classList.add('hidden');
      }
      return valid;
    }
  ];
  
  // --- Navigation events ---
  
  // Next button handler
  nextBtn.addEventListener('click', () => {
    // Validate before moving
    if (validators[currentStep] && !validators[currentStep]()) {
      // Focus first error
      const err = stepEls[currentStep].querySelector('.input-error:not(.hidden)');
      if (err) err.previousElementSibling.focus();
      return;
    }
    // Animate out
    stepEls[currentStep].classList.remove('animate-fade-in');
    stepEls[currentStep].classList.add('animate-fade-out');
    setTimeout(() => {
      currentStep++;
      showStep(currentStep);
      setButtons();
    }, 300);
  });
  
  // Prev button handler
  prevBtn.addEventListener('click', () => {
    if (currentStep === 0) return;
    // Animate out
    stepEls[currentStep].classList.remove('animate-fade-in');
    stepEls[currentStep].classList.add('animate-fade-out');
    setTimeout(() => {
      currentStep--;
      showStep(currentStep);
      setButtons();
    }, 300);
  });
  
  // Enter on form submits next
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    nextBtn.click();
  });
  
  // "Done" button (final screen)
  if (doneBtn) {
    doneBtn.addEventListener('click', () => {
      // Here you would redirect, close modal, or similar
      alert('Onboarding complete! Implement your redirect here.');
      // window.location.href = '/dashboard';
    });
  }
  
  // Steps.js nav clicking (optional: disables direct nav except completed steps)
  document.getElementById('onboarding-steps').addEventListener('click', (e) => {
    const nearest = e.target.closest('.stepsjs-step');
    if (!nearest) return;
    const i = Array.from(nearest.parentElement.children).indexOf(nearest);
    if (i < currentStep) {
      currentStep = i;
      showStep(currentStep, true);
      setButtons();
    }
  });
  
  // --- Init ---
  showStep(currentStep, false);
  setButtons();
  
  // --- UX touch: focus first input on each show
  stepEls.forEach((el, idx) => {
    el.addEventListener('animationend', () => {
      if (!el.classList.contains('hidden')) {
        const firstInput = el.querySelector('input, select');
        if (firstInput) firstInput.focus();
      }
    });
  });
  
  