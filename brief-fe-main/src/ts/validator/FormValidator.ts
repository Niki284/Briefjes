interface Validator {
  name: string
  message: string
  method: (field: HTMLInputElement | NodeList) => boolean
}

interface ValidatorWithField extends Validator {
  field: HTMLInputElement | NodeList
}

export class FormValidator {
  private validators: ValidatorWithField[] = []
  private errors: ValidatorWithField[] = []
  private form: HTMLFormElement
  private errorSummary: HTMLDivElement | null


  constructor(form: HTMLFormElement) {
    this.form = form
    this.errorSummary = this.form.querySelector('.error-summary')

    this.form.addEventListener("submit", (event) => this.onSubmit(event))
  }

  addValidator(validator: Validator): void {
    const field = this.form.elements.namedItem(validator.name)

    if (!field) {
      console.warn(`Field with name ${validator.name} not found`)
      return
    }

    this.validators.push({
      ...validator,
      field:
        field instanceof RadioNodeList ? field : (field as HTMLInputElement),
    })
  }

  validate(): boolean {
    this.errors = [];
    this.validators.forEach((validator) => {
      if (this.errors.some((error) => error.name === validator.name)) {
        return
      }

      if (!validator.method(validator.field)) {
        this.errors.push(validator)
      }
    })

    return this.errors.length === 0
  }

  onSubmit(event: Event): void {
    this.removeInlineErrors()
    this.resetSummary()
    this.validate()

    if (!this.validate()) {
      event.preventDefault()
      event.stopImmediatePropagation()
      this.showSummary()
      this.showInlineErrors()
    }
  }

  private createInlineError(error: ValidatorWithField): HTMLSpanElement {
    const newSpan = document.createElement("span")
    newSpan.className = "field-error"
    newSpan.innerText = error.message
    newSpan.id = `${error.name}-error`
    return newSpan
  }

  private removeInlineErrors(): void {
    this.form
      .querySelectorAll(".field-error")
      .forEach((element) => element.remove())

    this.errors.forEach((error) => {
      if (error.field instanceof NodeList) {
        error.field.forEach((node) => {
          const label = (node as HTMLInputElement).labels?.[0]
          if (label) {
            label.classList.remove("invalid")
            label.removeAttribute("aria-describedby")
            label.removeAttribute("aria-invalid")
          }
        })
      } else {
        const label = error.field.labels?.[0]
        if (label) {
          label.classList.remove("invalid")
          label.removeAttribute("aria-invalid")
        }
      }
    })

    this.errors = []
  }

  private showInlineErrors(): void {
    this.errors.forEach((error) => {
      const errorElement = this.createInlineError(error)

      if (error.field instanceof NodeList) {
        const radioGroupContainer = (
          error.field[0] as HTMLInputElement
        ).closest("fieldset")
        if (radioGroupContainer) {
          const legend = radioGroupContainer.querySelector("legend")
          if (legend) {
            legend.appendChild(errorElement)
            legend.classList.add("invalid")
            legend.setAttribute("aria-describedby", errorElement.id)
            legend.setAttribute("aria-invalid", "true")
          }
        }
      } else {
        const label = error.field.labels?.[ 0]
        if (label) {
          label.appendChild(errorElement)
          label.classList.add("invalid")
          label.setAttribute("aria-invalid", "true")
        }
      }
    })
  }

  private showSummary(): void {
    if (this.errorSummary) {
      const errorList = this.errorSummary.querySelector('ul')
      if (errorList) {
        errorList.innerHTML = '' // Clear existing errors
        this.errors.forEach((error) => {
          const listItem = document.createElement('li')
          listItem.innerText = error.message
          errorList.appendChild(listItem)
        })
      }
      this.errorSummary.style.display = 'block' // Show error summary
    }
  }

  private resetSummary(): void {
    if (this.errorSummary) {
      const errorList = this.errorSummary.querySelector('ul')
      if (errorList) {
        errorList.innerHTML = '' // Clear error list
      }
      this.errorSummary.style.display = 'none' // Hide error summary
    }
  }


}
