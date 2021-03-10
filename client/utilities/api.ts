
export class NotAuthorizedError {}

export const jsonFetch = (url: string, method: string = "GET", body?: Object) => fetch(url, {
  method,
  body: body ? JSON.stringify(body) : undefined,
  headers: {
    'content-type': 'application/json'
  },
  credentials: 'include'
})
  .then(assertAuthorized)
  .then(response => response.status === 204 || response.json())

const assertAuthorized = (response: Response) => {
  if (response.status === 401) {
    return Promise.reject(new NotAuthorizedError)
  }

  return response
}
