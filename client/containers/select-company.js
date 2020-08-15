
import React from 'react';
import {connect} from "react-redux";
import {selectCompany} from "actions/invoices";
import { List } from 'semantic-ui-react'


function SelectCompanyComponent({companies, selectCompany}) {
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

export const SelectCompany = connect(
  state => ({
    companies: state.invoices.companies
  }),
  {selectCompany}
)(SelectCompanyComponent)
