import { useApi } from "~/composables/useApi";

/**
 * Middleware global : protège les espaces authentifiés et empêche
 * un utilisateur déjà connecté de revoir les pages connexion/inscription.
 * Le contrôle de rôle fin (admin, medecin, patient) est fait via route.meta.roles
 * défini sur chaque page (voir definePageMeta dans pages/espace-client,
 * pages/espace-medecin et pages/espace-admin).
 */
export default defineNuxtRouteMiddleware(async (to) => {
  const { isLoggedIn, role, fetchMe, dashboardPath } = useAuth();
  const api = useApi();

  // Sur la première navigation (rechargement de page / SSR), on tente de
  // restaurer la session à partir du token stocké en cookie.
  if (!isLoggedIn.value && api.token.value) {
    await fetchMe();
  }

  const espacesProteges = ["/espace-client", "/espace-medecin", "/espace-admin", "/secretariat"];
  const estEspaceProtege = espacesProteges.some((p) => to.path.startsWith(p));
  const estPageInvite = to.path === "/connexion" || to.path === "/inscription";

  if (estEspaceProtege && !isLoggedIn.value) {
    return navigateTo({ path: "/connexion", query: { redirect: to.fullPath } });
  }

  if (estEspaceProtege && to.meta.roles) {
    const rolesAutorises = to.meta.roles as string[];
    if (!rolesAutorises.includes(role.value as string)) {
      return navigateTo(dashboardPath());
    }
  }

  if (estPageInvite && isLoggedIn.value) {
    return navigateTo(dashboardPath());
  }
});
