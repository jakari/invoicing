import { atom, useSetRecoilState } from "recoil"

export const isAuthenticatedState = atom<boolean>({
  key: 'isAuthenticatedState',
  default: false
})

export const useSetIsAuthenticated = () => useSetRecoilState(isAuthenticatedState)
