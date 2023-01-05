import { useRecoilCallback, useRecoilValue } from "recoil"
import { useApi } from "../api-helper"
import { selectedCompanyState, userSettingsState } from "./atoms"
import { Company } from "../records"


export const isCompanySelectedValue = () => useRecoilValue(selectedCompanyState)
export const setSelectCompany = () => {
  const api = useApi()

  return useRecoilCallback(({set}) => (company: Company) =>
    api.post(`/api/select-company/${company.id}`)
      .then(() => set(selectedCompanyState, company))
  )
}

export const useSettings = () => useRecoilValue(userSettingsState)
