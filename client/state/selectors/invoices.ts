import { selector, useRecoilValue } from "recoil"
import { userSettingsState } from "../atoms"
import { defaultInvoice } from "../../records"
import { dateToString } from "../../utilities/date"

const emptyInvoiceSelector = selector({
  key: "emptyInvoice",
  get: ({get}) => {
    const {default_values, templates} = get(userSettingsState)

    const now = new Date()
    const due = new Date()
    due.setDate(due.getDate()+default_values.default_due)

    return {
      ...defaultInvoice,
      remarkingTime: default_values.remarking_time,
      interestOnArrears: default_values.late_interest,
      created: dateToString(now),
      due: dateToString(due),
      template: templates[0].name
    }
  }
})

export const getEmptyInvoice = () => useRecoilValue(emptyInvoiceSelector)
