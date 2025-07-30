import { FormValidator } from "../../ts/validator/FormValidator.ts";
import { verifyAccessToken } from "../../ts/utils/auth.ts"
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

const updateNavigation = () => {
  const accessToken = verifyAccessToken();

  if (accessToken) {
    const nameInput = document.querySelector<HTMLInputElement>('#name');
    const approvedInput = document.querySelector<HTMLInputElement>('#approved');
    const organizationIdInput = document.querySelector<HTMLInputElement>('#organizationId');
    const emailInput = document.querySelector<HTMLInputElement>('#email');
    const idInput = document.querySelector<HTMLInputElement>('#userId');


    if (nameInput) nameInput.value = accessToken.name || '';
    if (approvedInput) approvedInput.value = accessToken.approved || '';
   // if (organizationIdInput) organizationIdInput.value = accessToken.organizations_id?.toString() || '';


    //if (organizationIdInput) organizationIdInput.value = accessToken.organizations_id || '';
    if (organizationIdInput) {
  organizationIdInput.value = accessToken.organizations_id !== undefined
    ? accessToken.organizations_id.toString()
    : '';
}

    if (emailInput) emailInput.value = accessToken.email || '';
    if (idInput) idInput.value = <string>(<unknown>accessToken.sub) || ""
    console.log(accessToken.sub);
  }
};

updateNavigation();

(() => {
  const form = document.querySelector('form') as HTMLFormElement;
  const succes = document.querySelector('.success') as HTMLElement;

  if (!form) {
    return;
  }

  const formValidator = new FormValidator(form);

  formValidator.addValidator({
    name: 'first-name',
    message: 'Gelieve je voornaam in te vullen (minimaal 2 tekens)',
    method: (field: HTMLInputElement | NodeList) => {
      const input = field as HTMLInputElement;
      return input.value.trim().length >= 2;
    }
  });

  formValidator.addValidator({
    name: 'last-name',
    message: 'Gelieve je achternaam in te vullen (minimaal 2 tekens)',
    method: (field: HTMLInputElement | NodeList) => {
      const input = field as HTMLInputElement;
      return input.value.trim().length >= 2;
    }
  });

  formValidator.addValidator({
    name: 'email',
    message: 'Gelieve een geldig e-mailadres in te vullen',
    method: (field: HTMLInputElement | NodeList) => {
      const input = field as HTMLInputElement;
      const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
      return emailRegex.test(input.value.trim());
    }
  });

  formValidator.addValidator({
    name: 'pickup-location',
    message: 'Gelieve een geldige locatie in te vullen (minimaal 2 tekens)',
    method: (field: HTMLInputElement | NodeList) => {
      const input = field as HTMLInputElement;
      const locationRegex = /^[a-zA-Z\u00C0-\u017F\s-']{2,}$/;
      return locationRegex.test(input.value.trim());
    }
  });

  formValidator.addValidator({
    name: 'dropoff-location',
    message: 'Gelieve een geldige bestemming in te vullen (minimaal 2 tekens)',
    method: (field: HTMLInputElement | NodeList) => {
      const input = field as HTMLInputElement;
      const locationRegex = /^[a-zA-Z\u00C0-\u017F\s-']{2,}$/;
      return locationRegex.test(input.value.trim());
    }
  });

  formValidator.addValidator({
    name: 'ride-date',
    message: 'Gelieve een geldige datum te selecteren (vandaag of in de toekomst)',
    method: (field: HTMLInputElement | NodeList) => {
      const input = field as HTMLInputElement;
      const selectedDate = new Date(input.value);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return selectedDate >= today &&
        selectedDate <= new Date(today.getFullYear() + 1, today.getMonth(), today.getDate());
    }
  });

  formValidator.addValidator({
    name: 'ride-time',
    message: 'Gelieve een geldige tijd te selecteren',
    method: (field: HTMLInputElement | NodeList) => {
      const input = field as HTMLInputElement;
      return input.value.trim().length > 0;
    }
  });

  form.addEventListener('submit', (event: Event) => {
    event.preventDefault();

    const formData = new FormData(form);

    const formDataObj: Record<string, string> = {};
    formData.forEach((value, key) => {
      formDataObj[key] = value as string;
    });
    console.log(formDataObj);

    handleSubmit(formDataObj);
    form.reset();
  });

  function handleSubmit(formDataObj: Record<string, string>) {
    fetch(`${API_BASE_URL}/bookings`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formDataObj),
    })
      .then(response => {
        if (!response.ok) {
          console.error('Form submission failed');
        } else {
          console.log('Form successfully submitted');
          succes.style.display = 'block';
        }
      })
      .catch(error => {
        console.error('Error submitting form:', error);
      });
  }
})();
