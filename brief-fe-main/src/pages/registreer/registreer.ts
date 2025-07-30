import { FormValidator } from "../../ts/validator/FormValidator.ts";
import { hasRole } from "../../ts/utils/auth.ts";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

if (hasRole("abonnee") || hasRole("beheerder")) {
  location.assign(`../?from=${window.location.href}`);
}

const registreerKlantForm = document.querySelector<HTMLFormElement>("#registreer-klant");
if (!registreerKlantForm) {
  throw new Error("Formulier niet gevonden");
}

// 🔒 Validatie
const formValidator = new FormValidator(registreerKlantForm);

formValidator.addValidator({
  name: "name",
  message: "Gelieve je voornaam in te vullen (minimaal 2 tekens)",
  method: (field) => (field as HTMLInputElement).value.trim().length >= 2,
});

formValidator.addValidator({
  name: "email",
  message: "Gelieve een geldig e-mailadres in te vullen",
  method: (field) => {
    const email = (field as HTMLInputElement).value.trim();
    const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return regex.test(email);
  },
});

formValidator.addValidator({
  name: "password",
  message: "Uw wachtwoord moet minimaal 8 tekens bevatten",
  method: (field) => (field as HTMLInputElement).value.trim().length >= 8,
});

// 📨 Event handler correct aanpakken (geen async direct)
registreerKlantForm.addEventListener("submit", (event) => {
  event.preventDefault();
  void handleSubmit(); // "void" zorgt dat ESLint niet klaagt over async-functie in addEventListener
});

async function handleSubmit() {
  const isValid = formValidator.validate();
  if (!isValid) {
    console.warn("Formulier bevat fouten.");
    return;
  }

  const formData = new FormData(registreerKlantForm!);
  const payload: Record<string, string | number> = {};

 formData.forEach((value, key) => {
  if (key === "organizations_id" || key === "role") {
    payload[key] = Number(value); // omzetten naar getal
  } else if (typeof value === "string") {
    payload[key] = value; // veilige toewijzing
  } else {
    console.warn(`Ongeldige waarde voor key ${key}:`, value);
  }
});


  // formData.forEach((value, key) => {
  //   if (key === "organization_id") {
  //     payload[key] = Number(value);
  //   } else {
  //     payload[key] = typeof value === "string" ? value : String();
  //     // payload[key] = value.toString();
  //   }
  // });

  try {
    const response = await fetch(`${API_BASE_URL}/users`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error("Registratie mislukt:", errorText);
      alert("Registratie is mislukt. Probeer opnieuw.");
      return;
    }

    console.log("Registratie gelukt!");
    location.assign("/pages/success/");
  } catch (error) {
    console.error("Netwerkfout bij registratie:", error);
    alert("Netwerkfout. Probeer later opnieuw.");
  }

  if (registreerKlantForm) {
    registreerKlantForm.reset();
  }
}
