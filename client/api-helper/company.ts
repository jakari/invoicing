import { useRecoilValue } from "recoil"
import { selectedCompanyState } from "../state/atoms"

/**
 * Builds API paths of the selected company. The company is part of the path, so a tab keeps working on its own
 * company whatever another tab selects.
 */
export const useCompanyPath = () => {
  const company = useRecoilValue(selectedCompanyState)

  return (path: string) => `/api/company/${company?.id}${path}`
}
