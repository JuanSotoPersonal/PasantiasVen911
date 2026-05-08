# 1. ACTUALIZAR SISTEMA
sudo apt update && sudo apt upgrade -y

# 2. INSTALAR DEPENDENCIAS NECESARIAS
sudo apt install apt-transport-https ca-certificates curl gnupg lsb-release -y

# 3. AGREGAR LLAVE GPG DE DOCKER
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# 4. CONFIGURAR REPOSITORIO (Linux Mint se basa en Ubuntu)
# Mint 21.x usa 'jammy', Mint 22.x usa 'noble'
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo $UBUNTU_CODENAME) stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# 5. INSTALAR DOCKER ENGINE Y DOCKER COMPOSE
sudo apt update
sudo apt install docker-ce docker-ce-cli containerd.io docker-compose-plugin -y

# 6. HABILITAR DOCKER SIN SUDO (Opcional pero recomendado)
sudo usermod -aG docker $USER
echo "INSTALACIÓN COMPLETADA. Por favor, reinicia sesión para aplicar los cambios de grupo."
