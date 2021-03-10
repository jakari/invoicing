import { useRecoilCallback } from "recoil"
import { isAuthenticatedState } from "state/auth"
import { jsonFetch, NotAuthorizedError } from "utilities/api"
import { mainLoadingState } from "state/atoms"

interface Options {
  useMainLoading: boolean
}

const defaultOptions: Options = {
  useMainLoading: true
}

export const useApi = (options?: Partial<Options>) => {
  const doRequest = useRequestApi(options)

  return {
    get: <T>(url: string) => doRequest<T>(url, "GET"),
    patch: <T>(url: string, body?: Object) => doRequest<T>(url, "PATCH", body),
    post: <T>(url: string, body?: Object) => doRequest<T>(url, "POST", body)
  }
}

const useRequestApi = (options?: Partial<Options>) => {
  const mergedOpts = options ? {...defaultOptions, ...options} : defaultOptions

  return useRecoilCallback(({set}) => async <T = any>(url: string, method: string = "GET", body?: Object): Promise<T> => {
    try {
      if (mergedOpts.useMainLoading) set(mainLoadingState, true)
      return await jsonFetch(url, method, body)
    } catch (e) {
      if (e instanceof NotAuthorizedError) {
        set(isAuthenticatedState, false)
      }

      throw e
    } finally {
      if (mergedOpts.useMainLoading) set(mainLoadingState, false)
    }
  }, [])
}
