import { useRecoilCallback, useSetRecoilState } from "recoil"
import { selectedCompanyState, userSettingsState } from "../state/atoms"
import { isAuthenticatedState, useSetIsAuthenticated } from "../state/auth"
import { useCallback } from "react"
import { AppConfiguration } from "../records"
import { useApi } from "api"

export const fetchUserSettings = () => {
  const {get} = useApi()
  const setUserSettings = useSetRecoilState(userSettingsState)
  const setIsAuthenticated = useSetIsAuthenticated()
  const selectCompany = useSetRecoilState(selectedCompanyState)

  return useCallback(async () => {
    try {
      const settings = (await get('/api/invoice/settings')) as AppConfiguration
      setUserSettings(settings)
      setIsAuthenticated(true)
      if (settings.selected_company) selectCompany(settings.selected_company)
    } catch (e) {
    }
  }, [])
}

export const userLoginCallback = () => {
  const {post} = useApi()

  return useRecoilCallback(({set}) => (username: string, password: string) => {
    post('/api/login', {username, password})
      .then(response => {
        set(isAuthenticatedState, true)
        set(userSettingsState, response)
      })
  }, [])
}

export const useLogout = () => {
  const {post} = useApi()

  return useRecoilCallback(({set, reset}) => async () => {
    await post('/api/logout')
    set(isAuthenticatedState, false)
    reset(userSettingsState)
    reset(selectedCompanyState)
  })
}
