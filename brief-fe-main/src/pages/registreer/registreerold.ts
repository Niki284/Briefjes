// import { FormValidator } from "../../ts/validator/FormValidator.ts";
// import { hasRole } from "../../ts/utils/auth.ts";

// const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

// // Als gebruiker al is ingelogd, redirect naar homepage
// if (hasRole("abonnee") || hasRole("beheerder")) {
//   location.assign(`../?from=${window.location.href}`);
// }

// // Selecteer formulier en elementen
// const registreerKlantForm = document.querySelector<HTMLFormElement>("#registreer-klant") as HTMLFormElement;

// // Validatie opzetten
// if (registreerKlantForm) {
//   const formValidatorKlant = new FormValidator(registreerKlantForm);

//   formValidatorKlant.addValidator({
//     name: 'name',
//     message: 'Gelieve je voornaam in te vullen (minimaal 2 tekens)',
//     method: (field: HTMLInputElement | NodeList) => {
//       const input = field as HTMLInputElement;
//       return input.value.trim().length >= 2;
//     }
//   });

//   formValidatorKlant.addValidator({
//     name: 'email',
//     message: 'Gelieve een geldig e-mailadres in te vullen',
//     method: (field: HTMLInputElement | NodeList) => {
//       const input = field as HTMLInputElement;
//       const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
//       return emailRegex.test(input.value.trim());
//     }
//   });

//   formValidatorKlant.addValidator({
//     name: 'password',
//     message: 'Uw wachtwoord moet minimaal 8 tekens bevatten',
//     method: (field: HTMLInputElement | NodeList) => {
//       const input = field as HTMLInputElement;
//       return input.value.trim().length >= 8;
//     }
//   });

//   const formData = new FormData(registreerKlantForm);
// const formDataObj: Record<string, string | number> = {};

// formData.forEach((value, key) => {
//   if (key === "organization_id") {
//     formDataObj[key] = Number(value); // cast naar nummer
//   } else {
//     formDataObj[key] = value.toString();
//   }
// });

//   // Submit handler
// registreerKlantForm.addEventListener('submit', (event: Event) => {
//   event.preventDefault();

//   const isValid = formValidatorKlant.validate();

//   if (!isValid) {
//     console.error('Formulier bevat fouten. Corrigeer deze eerst.');
//     return;
//   }

//   const formData = new FormData(registreerKlantForm);
//   const formDataObj: Record<string, string | number> = {};

//   formData.forEach((value, key) => {
//     // Zet organisatie-id om naar number
//     if (key === "organization_id") {
//       formDataObj[key] = Number(value);
//     } else {
//       formDataObj[key] = value.toString();
//     }
//   });

//   handleKlantSubmit(formDataObj);
//   registreerKlantForm.reset();
// });

//   // Verzenden naar de backend
//   function handleKlantSubmit(formDataObj: Record<string, string>) {
//     fetch(`${API_BASE_URL}/users`, {
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json',
//       },
//       body: JSON.stringify(formDataObj),
//     })
//       .then(response => {
//         if (!response.ok) {
//           console.error('Registratie mislukt');
//         } else {
//           console.log('Registratie gelukt');
//           location.assign("/success/");
//         }
//       })
//       .catch(error => {
//         console.error('Fout bij verzenden formulier:', error);
//       });
//   }
// }




//  // // Submit handler
//   // registreerKlantForm.addEventListener('submit', (event: Event) => {
//   //   event.preventDefault();

//   //   const isValid = formValidatorKlant.validate();

//   //   if (!isValid) {
//   //     console.error('Formulier bevat fouten. Corrigeer deze eerst.');
//   //     return;
//   //   }

//   //   const formData = new FormData(registreerKlantForm);
//   //   const formDataObj: Record<string, string> = {};

//   //   formData.forEach((value, key) => {
//   //     formDataObj[key] = value as string;
//   //   });

//   //   handleKlantSubmit(formDataObj);
//   //   registreerKlantForm.reset();
//   // });