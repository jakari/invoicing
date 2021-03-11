
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
  .then(async response => {
    if (!response.ok) throw new ResponseError(response)
    return response.status === 204 || response.json()
  })

const assertAuthorized = (response: Response) => {
  if (response.status === 401) {
    return Promise.reject(new NotAuthorizedError)
  }

  return response
}

export class ResponseError extends Error {
  response: Response

  constructor(response: Response) {
    super("Error from response, status: " + response.status)

    this.response = response
  }
}
