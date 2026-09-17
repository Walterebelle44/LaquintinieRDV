pipeline {
    agent any

    environment {
        DEPLOY_PATH = '/var/www/gamestore-cameroun'
        BRANCH_PROD = 'main'
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/Walterebelle44/LaquintinieRDV.git', credentialsId: 'github-https-token'
            }
        }

        stage('Install Backend Deps') {
            steps {
                dir('backend') {
                    bat 'composer install --optimize-autoloader'
                }
            }
        }

        stage('Install Frontend Deps (Nuxt)') {
            steps {
                dir('frontend') {
                    bat 'npm ci'
                }
            }
        }

        stage('Run Tests') {
            steps {
                dir('backend') {
                    bat 'copy .env.testing .env'
                    bat 'php artisan key:generate'
                    bat 'php artisan test'
                }
            }
        }

        stage('Build Frontend') {
            steps {
                dir('frontend') {
                    bat 'npm run build'
                }
            }
        }

        // Stage Deploy retiré pour le moment (pas encore de serveur cible).
        // Quand tu seras prêt : soit un déploiement local (copy/robocopy sans ssh
        // si Jenkins tourne sur la même machine que le serveur), soit un
        // déploiement distant en SSH (credential 'deploy-server-key' à créer).
    }

    post {
        success {
            echo '✅ Build réussi !'
        }
        failure {
            echo '❌ Le pipeline a échoué.'
        }
    }
}