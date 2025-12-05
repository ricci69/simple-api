#!/bin/bash

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Help
show_help() {
    echo "Usage: $0 [options]"
    echo ""
    echo "Options:"
    echo "  -e, --email EMAIL        User email"
    echo "  -n, --name NAME          User name"
    echo "  -p, --password PASSWORD  Password"
    echo "  -r, --role ROLE          Role (user/admin) [default: user]"
    echo "  -h, --help               Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 --email test@test.com --name \"TestUser\" --password pass123"
    echo "  $0 -e test@test.com -n TestUser -p pass123 -r admin"
    echo "  $0  (interactive mode)"
    exit 0
}

# Parse arguments
EMAIL=""
NAME=""
PASSWORD=""
ROLE="user"
ROLE_SET_BY_ARG=0

while [[ $# -gt 0 ]]; do
    case $1 in
        -e|--email)
            EMAIL="$2"
            shift 2
            ;;
        -n|--name)
            NAME="$2"
            shift 2
            ;;
        -p|--password)
            PASSWORD="$2"
            shift 2
            ;;
        -r|--role)
            ROLE="$2"
            ROLE_SET_BY_ARG=1   # role explicitly provided
            shift 2
            ;;
        -h|--help)
            show_help
            ;;
        *)
            echo -e "${RED}✗ Unknown option: $1${NC}"
            show_help
            ;;
    esac
done

# Prompt for missing data interactively
if [ -z "$EMAIL" ]; then
    read -p "Email: " EMAIL
fi

if [ -z "$NAME" ]; then
    read -p "Name: " NAME
fi

if [ -z "$PASSWORD" ]; then
    read -sp "Password: " PASSWORD
    echo
fi

# Only prompt for role if it was NOT provided as argument
if [ "$ROLE_SET_BY_ARG" -eq 0 ]; then
    read -p "Role (user/admin) [user]: " role_input
    if [ ! -z "$role_input" ]; then
        ROLE="$role_input"
    fi
fi

# Validations
if [ -z "$EMAIL" ]; then
    echo -e "${RED}✗ Email is required${NC}"
    exit 1
fi

if [ -z "$NAME" ]; then
    echo -e "${RED}✗ Name is required${NC}"
    exit 1
fi

if [ -z "$PASSWORD" ]; then
    echo -e "${RED}✗ Password is required${NC}"
    exit 1
fi

# Basic email validation
if [[ ! "$EMAIL" =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
    echo -e "${RED}✗ Invalid email${NC}"
    exit 1
fi

# Password length validation
if [ ${#PASSWORD} -lt 6 ]; then
    echo -e "${RED}✗ Password must be at least 6 characters${NC}"
    exit 1
fi

# Role validation
if [ "$ROLE" != "user" ] && [ "$ROLE" != "admin" ]; then
    echo -e "${RED}✗ Role must be 'user' or 'admin'${NC}"
    exit 1
fi

# Database path
DB_PATH="data/api.db"

if [ ! -f "$DB_PATH" ]; then
    echo -e "${RED}✗ Database not found: $DB_PATH${NC}"
    exit 1
fi

# Check if user already exists
EXISTING=$(sqlite3 "$DB_PATH" "SELECT COUNT(*) FROM users WHERE email='$EMAIL';")
if [ "$EXISTING" -gt 0 ]; then
    echo -e "${RED}✗ User with email '$EMAIL' already exists${NC}"
    exit 1
fi

echo -e "${YELLOW}⏳ Creating user...${NC}"

# Generate password hash
HASH=$(php -r "echo password_hash('$PASSWORD', PASSWORD_DEFAULT);")

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Error generating password hash${NC}"
    exit 1
fi

# Insert user
sqlite3 "$DB_PATH" "INSERT INTO users (email, password, name, role) VALUES ('$EMAIL', '$HASH', '$NAME', '$ROLE');"

if [ $? -eq 0 ]; then
    USER_ID=$(sqlite3 "$DB_PATH" "SELECT id FROM users WHERE email='$EMAIL';")
    echo ""
    echo -e "${GREEN}✓ User created successfully!${NC}"
    echo -e "  ID:    ${GREEN}$USER_ID${NC}"
    echo -e "  Email: ${GREEN}$EMAIL${NC}"
    echo -e "  Name:  ${GREEN}$NAME${NC}"
    echo -e "  Role:  ${GREEN}$ROLE${NC}"
    echo ""
else
    echo -e "${RED}✗ Error creating user${NC}"
    exit 1
fi
