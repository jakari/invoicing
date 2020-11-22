
import invoices from './invoices'
import auth from './auth'
import util from './util'
import { AppConfiguration, Auth } from "client/records"

export default {
  invoices,
  auth,
  util
};

export interface AppState {
  auth: Auth
  invoices: AppConfiguration
}
