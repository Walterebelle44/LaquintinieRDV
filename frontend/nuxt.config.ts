// https://nuxt.com/docs/api/configuration/nuxt-config
import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: ['~/assets/css/tailwind.css'],
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
  runtimeConfig: {
    public: {
      // URL de l'API Laravel. Redéfinissable via la variable d'env NUXT_PUBLIC_API_BASE
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
    },
  },
  app: {
    head: {
      title: "MediRDV Laquintinie — Prise de rendez-vous en ligne",
      htmlAttrs: { lang: "fr" },
      meta: [
        { charset: "utf-8" },
        { name: "viewport", content: "width=device-width, initial-scale=1" },
        {
          name: "description",
          content:
            "Prenez rendez-vous en ligne avec les médecins de l'Hôpital Laquintinie de Douala. Simple, rapide et sécurisé.",
        },
      ],
      link: [
        { rel: "icon", type: "image/svg+xml", href: "/favicon.svg" },
        { rel: "preconnect", href: "https://fonts.googleapis.com" },
        { rel: "preconnect", href: "https://fonts.gstatic.com", crossorigin: "" },
        {
          rel: "stylesheet",
          href: "https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap",
        },
      ],
    },
  }
})
