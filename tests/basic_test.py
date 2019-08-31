from selenium import webdriver
# from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import sys

if len(sys.argv) > 1:
    host = sys.argv[1]
else:
    host = 'http://127.0.0.1:8080'

# driver = webdriver.Chrome(ChromeDriverManager().install())
driver = webdriver.Chrome()
wait = WebDriverWait(driver, 10)


def bypass_preloader():
    wait.until(EC.invisibility_of_element_located((By.CLASS_NAME, 'pre-loader')))


def find_clickable(by, text):
    return wait.until(EC.element_to_be_clickable((by, text)))


def helper_logout():
    wait.until(EC.presence_of_element_located((By.LINK_TEXT, "Logout")))
    bypass_preloader()
    logout_btn = wait.until(EC.element_to_be_clickable((By.LINK_TEXT, "Logout")))
    logout_btn.click()


def helper_login(t_email, t_password):
    login_url = '{}/login'.format(host)
    driver.get(login_url)
    bypass_preloader()
    email = find_clickable(By.NAME, 'email')
    password = find_clickable(By.NAME, 'pass')
    submit_btn = find_clickable(By.CSS_SELECTOR, 'button[type=submit]')
    email.send_keys(t_email)
    password.send_keys(t_password)
    submit_btn.click()


def test_guest_login():
    print("Testing guest login...")
    driver.get(host)  # Load homepage
    bypass_preloader()
    guest_login_btn = find_clickable(By.LINK_TEXT, "try it without login")
    guest_login_btn.click()
    helper_logout()
    print("  \033[36mPassed\033[0m")


def test_login():
    print("Testing login...")
    print("- Testing successful login...")
    helper_login('muntashir.islam96@gmail.com', 'ok')
    helper_logout()
    print("- Testing failed login...")
    helper_login('invalid@email', 'ok')
    invalid_login_alert = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, '.alert.alert-danger')))
    assert "Login failed! Please try again with valid email and password or create an account if you don't have one."\
           in invalid_login_alert.text
    bypass_preloader()
    print("  \033[36mPassed\033[0m")


# To test
# - Register
# - Feedback
def main():
    failed = False
    try:
        print('Testing site: {}\n'.format(host))
        test_guest_login()
        test_login()
    except:
        print("  \033[31mFailed\033[0m".format())
        failed = True
    finally:
        driver.quit()
    return 1 if failed else 0


if __name__ == '__main__':
    sys.exit(main())
