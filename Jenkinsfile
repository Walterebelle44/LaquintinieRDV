pipeline {
    agent any

    environment {
        DEPLOY_PATH = '/var/www/gamestore-cameroun'
        BRANCH_PROD = 'main'
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/Walterebelle44/LaquintinieRDV.git', credentialsId: 'eac688b3-3279-4726-a137-7938a9d36603'
            }
        }

        stage('Install Backend Deps') {
            steps {
                sh 'composer install --no-dev --optimize-autoloader'
            }
        }

        stage('Install Frontend Deps (Nuxt)') {
            steps {
                dir('frontend') {
                    sh 'npm ci'
                }
            }
        }

        stage('Run Tests') {
            steps {
                sh 'cp .env.testing .env'
                sh 'php artisan key:generate'
                sh 'php artisan test'
            }
        }

        stage('Build Frontend') {
            steps {
                dir('frontend') {
                    sh 'npm run build'
                }
            }
        }

        // Stage Deploy retiré pour le moment (pas encore de serveur cible).
        // Quand tu seras prêt : soit un déploiement local (cp/rsync sans ssh
        // si Jenkins tourne sur la même machine que le serveur), soit un
        // déploiement distant en SSH (credential 'deploy-server-key' à créer).
    }

    post {
        success {
            echo '✅ Déploiement réussi !'
        }
        failure {
            echo '❌ Le pipeline a échoué.'
        }
    }
}