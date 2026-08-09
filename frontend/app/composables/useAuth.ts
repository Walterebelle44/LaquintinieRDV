export type Role = "client" | "medecin" | "admin" | null;

export const useAuth = () => {
  const isLoggedIn = useState<boolean>("auth-logged-in", () => false);
  const role = useState<Role>("auth-role", () => null);
  const name = useState<string>("auth-name", () => "Walter Djoko");

  function login(r: Role = "client") {
    isLoggedIn.value = true;
    role.value = r;
  }
  function logout() {
    isLoggedIn.value = false;
    role.value = null;
  }
  return { isLoggedIn, role, name, login, logout };
};
