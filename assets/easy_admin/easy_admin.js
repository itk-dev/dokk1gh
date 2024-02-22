import './easy_admin.scss'

import bootstrap from 'bootstrap/dist/js/bootstrap.bundle'

// @see https://getbootstrap.com/docs/5.3/components/tooltips/#enable-tooltips
document.querySelectorAll('[data-bs-toggle="tooltip"]')
  .forEach(el => new bootstrap.Tooltip(el))

window.addEventListener('load', () => {
  document.querySelectorAll('[data-clipboard-text]').forEach((el) => {
    if (navigator.clipboard) {
      el.addEventListener('click', () => {
        const text = el.dataset.clipboardText
        navigator.clipboard.writeText(text)
          .then(() => {
            if (!el.tooltip) {
              el.tooltip = new bootstrap.Tooltip(el, {
                title: el.dataset.clipboardSuccess ?? `"${text}" was copied to your clipboard.`
              })
              el.tooltip.show()
            }
          })
          .catch(err => {
            console.error(el.dataset.clipboardError ?? `Error copying text to clipboard: ${err}`)
          })
      })
    } else {
      el.hidden = true
    }
  })

  if (document.body.classList.contains('ea-new-Code')) {
    // Show modal when creating a new code.
    document.querySelector('form[id="new-Code-form"]').addEventListener('submit', () => {
      const wrapper = document.querySelector('.wrapper')
      if (wrapper) {
        wrapper.classList.add('blur')
      }
      const modalElement = document.getElementById('saving')
      if (modalElement) {
        // https://getbootstrap.com/docs/5.3/components/modal/#via-javascript
        new bootstrap
          .Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
          })
          .show()
      }
    })

    const updateTimerange = () => {
      const startTime = document.getElementById('Code_startTime-picker').valueAsDate
      const endTime = new Date(startTime.getTime())
      const timeStart = document.getElementById('Code_endTime-time-start').value
      const timeEnd = document.getElementById('Code_endTime-time-end').value

      let numbers = timeStart.split(':').map(s => parseInt(s))
      startTime.setHours(numbers[0], numbers[1], 0)

      numbers = timeEnd.split(':').map(s => parseInt(s))
      endTime.setHours(numbers[0], numbers[1], 0)

      document.getElementById('Code_startTime').valueAsDate = startTime
      document.getElementById('Code_endTime').valueAsDate = endTime
    }

    [
      'Code_startTime-picker',
      'Code_endTime-time-start',
      'Code_endTime-time-end'
    ].forEach(id => document.getElementById(id)?.addEventListener('change', updateTimerange))
    updateTimerange()
  }

  // Hook up expire actions to submit via modal (cf. vendor/easycorp/easyadmin-bundle/assets/js/app.js)
  document.querySelectorAll('.action-expire-app').forEach((actionElement) => {
    actionElement.addEventListener('click', (event) => {
      event.preventDefault()

      document.querySelector('#modal-expire-app-button').addEventListener('click', () => {
        const formAction = actionElement.getAttribute('formaction')
        const form = document.querySelector('#expire-app-form')
        form.setAttribute('action', formAction)
        form.submit()
      })
    })
  })

  // Time ranges on guests.
  if (document.body.classList.contains('ea-new-Guest') ||
        document.body.classList.contains('ea-edit-Guest')) {
    const defaultValues = (() => {
      const el = document.querySelector('[name="Guest[timeRanges][default_values]"]')
      try {
        return JSON.parse(el.value)
      } catch (ex) {
        return {}
      }
    })()

    const updateRow = (event) => {
      const el = event.target
      const controls = el.closest('tr').querySelectorAll('select')
      const checked = el.checked
      const day = el.dataset.day
      if (checked) {
        controls.forEach((control, index) => {
          if (!control.value) {
            // Restore selected value or use default.
            control.value = control.dataset.last_selected ||
                            (defaultValues[day]?.[index] ?? '')
          }
          control.disabled = false
        })
      } else {
        // Reset choices, but remember selected value.
        controls.forEach(control => {
          control.dataset.last_selected = control.value
          control.value = ''
          control.disabled = true
        })
      }
    }

    document.querySelectorAll('#time-ranges [type="checkbox"]').forEach((el, index) => {
      const controls = el.closest('tr').querySelectorAll('select')
      el.addEventListener('change', updateRow)
      el.dataset.day = index + 1
      el.checked = controls[0]?.value || controls[1]?.value
      el.dispatchEvent(new Event('change'))
    })
  }
})
