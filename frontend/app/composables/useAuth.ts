import { useApi } from "./useApi";

export type Role = "admin" | "medecin" | "patient" | "secretaire" | null;

export interface AuthUser {
  id: number;
  prenom: string;
  nom: string;
  email: string;
  telephone: string;
  photo: string | null;
  statut: string;
  roles: Role[];
  medecin_id: number | null;
}

/**
 * Authentification réelle contre l'API Laravel (Sanctum).
 * Le token est conservé dans un cookie (SSR-safe) ; l'utilisateur courant
 * en useState partagé côté client.
 */
export const useAuth = () => {
  const api = useApi();
  const user = useState<AuthUser | null>("auth-user", () => null);

  const isLoggedIn = computed(() => !!user.value);
  const role = computed<Role>(() => (user.value?.roles?.[0] as Role) ?? null);
  const name = computed(() => (user.value ? `${user.value.prenom} ${user.value.nom}` : ""));

  async function login(email: string, motDePasse: string) {
    const res = await api.post<{ user: AuthUser; token: string }>("/connexion", {
      email,
      mot_de_passe: motDePasse,
    });
    api.token.value = res.token;
    user.value = res.user;
    return res.user;
  }

  async function register(payload: {
    prenom: string;
    nom: string;
    email: string;
    telephone: string;
    mot_de_passe: string;
    mot_de_passe_confirmation: string;
  }) {
    const res = await api.post<{ user: AuthUser; token: string }>("/inscription", payload);
    api.token.value = res.token;
    user.value = res.user;
    return res.user;
  }

  async function fetchMe() {
    if (!api.token.value) {
      user.value = null;
      return null;
    }
    try {
      const res = await api.get<{ user: AuthUser }>("/moi");
      user.value = res.user;
      return res.user;
    } catch {
      api.token.value = null;
      user.value = null;
      return null;
    }
  }

  async function logout() {
    try {
      await api.post("/deconnexion");
    } catch {
      // on nettoie l'état local même si l'appel échoue (token déjà expiré, etc.)
    }
    api.token.value = null;
    user.value = null;
  }

  /** Chemin du tableau de bord correspondant au rôle courant. */
  function dashboardPath(): string {
    switch (role.value) {
      case "admin":
        return "/espace-admin";
      case "medecin":
        return "/espace-medecin";
      case "secretaire":
        return "/secretariat";
      default:
        return "/espace-client";
    }
  }

  return { user, isLoggedIn, role, name, login, register, logout, fetchMe, dashboardPath };
};
