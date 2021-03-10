
export function dateToString(date: Date): string {
  return date.toISOString().substr(0, 10);
}
