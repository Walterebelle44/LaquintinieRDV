/**
 * Client API central pour communiquer avec le backend Laravel.
 * Attache automatiquement le token Sanctum (stocké en cookie) et
 * préfixe toutes les requêtes avec l'URL de l'API (runtimeConfig.public.apiBase).
 */
export const useApi = () => {
  const config = useRuntimeConfig();
  const token = useCookie<string | null>("medirdv_token", {
    default: () => null,
    sameSite: "lax",
    maxAge: 60 * 60 * 24 * 7,
  });

  const request = async <T = any>(path: string, opts: any = {}): Promise<T> => {
    const { headers: extraHeaders, ...restOpts } = opts;
    try {
      return await $fetch<T>(path, {
        baseURL: config.public.apiBase,
        ...restOpts,
        headers: {
          Accept: "application/json",
          ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
          ...(extraHeaders || {}),
        },
      });
    } catch (e: any) {
      // Normalise l'erreur pour un affichage simple côté UI (message + erreurs de validation Laravel)
      const status = e?.response?.status;
      const data = e?.response?._data || e?.data;

      if (status === 401) {
        token.value = null;
      }

      throw {
        status,
        message: data?.message || "Une erreur est survenue. Merci de réessayer.",
        errors: data?.errors || null,
      };
    }
  };

  return {
    token,
    get: <T = any>(path: string, opts: any = {}) => request<T>(path, { method: "GET", ...opts }),
    post: <T = any>(path: string, body?: any, opts: any = {}) => request<T>(path, { method: "POST", body, ...opts }),
    put: <T = any>(path: string, body?: any, opts: any = {}) => request<T>(path, { method: "PUT", body, ...opts }),
    patch: <T = any>(path: string, body?: any, opts: any = {}) => request<T>(path, { method: "PATCH", body, ...opts }),
    del: <T = any>(path: string, opts: any = {}) => request<T>(path, { method: "DELETE", ...opts }),
  };
};
