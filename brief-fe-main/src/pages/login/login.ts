import { login } from "../../ts/utils/auth.ts"
import { FormValidator } from "../../ts/validator/FormValidator.ts"
import { LoginPayload } from "../../ts/types/index.ts"
import { hasRole} from "../../ts/utils/auth.ts"

if (hasRole("beheerder") || hasRole("abonnee")) {
  location.assign(`../?from=${window.location.href}`)
}



const loginForm = document.querySelector<HTMLFormElement>(
  "#login",
) as HTMLFormElement

if (loginForm) {
  const formValidator = new FormValidator(loginForm)

  formValidator.addValidator({
    name: "email",
    message: "Gelieve een geldig e-mailadres in te vullen",
    method: (field: HTMLInputElement | NodeList) => {
      const input = field as HTMLInputElement
      const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/
      return emailRegex.test(input.value.trim())
    },
  })

  formValidator.addValidator({
    name: "password",
    message: "Password must be at least 4 characters long.",
    method: (field) => {
      const input = field as HTMLInputElement
      return input.value.length >= 4
    },
  })

  loginForm.addEventListener("submit", (event: Event) => {
    event.preventDefault()

    const formData = new FormData(loginForm)
    const formDataObj: Record<string, string> = {}
    formData.forEach((value, key) => {
      formDataObj[key] = value as string
    })
    console.log(formDataObj)

    const email = formDataObj.email
    const password = formDataObj.password

    handleLogin({ email, password })
  })

  function handleLogin(payload: LoginPayload) {
    login(payload)
      .then(() => {
        console.log("Login successful")
        location.assign("/pages/channels/channels.html")
      })
      .catch((error) => {
        console.error("Login failed:", error)
      })
  }
}
