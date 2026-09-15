import type { ApiErrorBody } from '~/types/api'

/**
 * Erreur d'appel API, normalisée à partir de l'enveloppe d'erreur du backend.
 * `status` vaut 0 quand le serveur n'a pas pu être joint (réseau, CORS).
 */
export class ApiRequestError extends Error {
  constructor(
    public readonly status: number,
    public readonly code: string,
    message: string,
    public readonly errors: Record<string, string[]> = {},
  ) {
    super(message)
    this.name = 'ApiRequestError'
  }

  /** Erreur de validation portant sur des champs précis. */
  get hasFieldErrors(): boolean {
    return Object.keys(this.errors).length > 0
  }
}

type FetchOptions = NonNullable<Parameters<typeof $fetch>[1]>

export function useApi() {
  const { public: { apiBase } } = useRuntimeConfig()

  async function request<T>(path: string, options: FetchOptions = {}): Promise<T> {
    try {
      return await $fetch<T>(path, {
        baseURL: apiBase,
        // Accept fait partie des en-têtes « simples » : un GET reste sans préflight CORS.
        headers: { Accept: 'application/json' },
        ...options,
      }) as T
    }
    catch (error: unknown) {
      const response = (error as { response?: { status?: number } })?.response
      const body = (error as { data?: Partial<ApiErrorBody> })?.data
      const status = response?.status ?? 0

      throw new ApiRequestError(
        status,
        body?.code ?? (status ? 'REQUEST_ERROR' : 'NETWORK_ERROR'),
        body?.message
          ?? (status
            ? 'Une erreur est survenue. Veuillez réessayer.'
            : 'Impossible de joindre le serveur. Vérifiez votre connexion et réessayez.'),
        body?.errors ?? {},
      )
    }
  }

  return { request }
}
