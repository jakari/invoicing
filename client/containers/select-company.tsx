
import React from "react"
import { List } from "semantic-ui-react"
import { useRecoilValue } from "recoil"
import { setSelectCompany } from "state/user-settings"
import { userSettingsState } from "state/atoms"


export function SelectCompany() {
  const {companies} = useRecoilValue(userSettingsState)
  const selectCompany = setSelectCompany()

  return (
    <>
      <h1 className="ui header">Valitse yritys</h1>
      <List selection verticalAlign='middle'>
        {companies.map(company => (
          <List.Item key={"company-" + company.id}>
            <List.Content onClick={() => selectCompany(company)}>
              <List.Header>{company.name}</List.Header>
            </List.Content>
          </List.Item>
        ))}
      </List>
    </>
  )
}
