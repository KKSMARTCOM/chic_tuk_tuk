/** Nombre au format français — « 2 400 ». */
export function formatNumber(value: number): string {
  return value.toLocaleString('fr-FR')
}

/** Montant en francs CFA — « 2 400 FCFA ». */
export function formatFcfa(amount: number): string {
  return `${formatNumber(amount)} FCFA`
}

/** Date locale au format AAAA-MM-JJ. (toISOString convertirait en UTC et décalerait le jour.) */
export function toLocalIsoDate(date: Date): string {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}
