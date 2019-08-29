# Test new project page
#
# This script currently tests FASTA file sources only
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
from basic_test import helper_login, driver, bypass_preloader, find_clickable, helper_logout, host
import os
import sys

wait = WebDriverWait(driver, 100)
fasta_file = 'assembled-fish_mito.fasta'
fasta_zip = '6 sequences.zip'


def get_script_path():
    return os.path.dirname(os.path.realpath(sys.argv[0]))


def test_examples():
    print("Testing examples...")
    driver.get('{}/projects/new'.format(host))
    bypass_preloader()
    example1 = find_clickable(By.CSS_SELECTOR, 'button.small.uppercase:nth-child(1)')
    example2 = find_clickable(By.CSS_SELECTOR, 'button.small.uppercase:nth-child(2)')
    print("- Testing Example 1...")
    example1.click()
    wait.until(EC.visibility_of_element_located((By.ID, "input_seq")))
    fasta_msg = wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#fasta_status .text-success")))
    assert "Found: 12 FASTA Sequences" in fasta_msg.text
    print("- Testing Example 2...")
    example2.click()
    wait.until(EC.visibility_of_element_located((By.ID, "input_seq")))
    fasta_msg = wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#fasta_status .text-success")))
    assert "Found: 15 FASTA Sequences" in fasta_msg.text
    print("  \033[36mPassed\033[0m")


def test_input_sequence():
    print("Testing input sequences...")
    driver.get('{}/projects/new'.format(host))
    bypass_preloader()
    select = Select(driver.find_element_by_id('method'))
    select.select_by_value('input_seq')
    wait.until(EC.visibility_of_element_located((By.ID, "input_seq")))
    driver.execute_script('document.getElementById("seq_text").value="{}";'.format(
        open('{}/{}'.format(get_script_path(), fasta_file), 'r').read().replace('\n', '\\n\\\n')
    ))
    analyze_btn = find_clickable(By.ID, 'analyze_seq_text')
    analyze_btn.click()
    fasta_msg = wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#fasta_status .text-success")))
    assert "Found: 25 FASTA Sequences" in fasta_msg.text
    print("  \033[36mPassed\033[0m")


def test_upload_file():
    print("Testing upload file...")
    driver.get('{}/projects/new'.format(host))
    bypass_preloader()
    select = Select(driver.find_element_by_id('method'))
    select.select_by_value('upload_file')
    print("- Testing FASTA file upload...")
    file = wait.until(EC.presence_of_element_located((By.ID, "filef")))
    file.send_keys('{}/{}'.format(get_script_path(), fasta_file))
    upload_msg = wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#filef_status .alert.alert-success")))
    assert "Upload success! The file was uploaded successfully." in upload_msg.text
    fasta_msg = wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#fasta_status .text-success")))
    assert "Found: 25 FASTA Sequences" in fasta_msg.text
    print("- Testing Zip file upload...")
    reupload_btn = find_clickable(By.ID, "upload_new")
    reupload_btn.click()
    file.send_keys('{}/{}'.format(get_script_path(), fasta_zip))
    upload_msg = wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#filef_status .alert.alert-success")))
    assert "Upload success! The file was uploaded successfully." in upload_msg.text
    fasta_msg = wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#fasta_status .text-success")))
    assert "Found: 6 FASTA Sequences" in fasta_msg.text
    print("  \033[36mPassed\033[0m")


def test_input_accn_gid():
    print("Testing ACCN/GID...")
    driver.get('{}/projects/new'.format(host))
    bypass_preloader()
    select = Select(driver.find_element_by_id('method'))
    select.select_by_value('input_accn_gin')
    wait.until(EC.visibility_of_element_located((By.ID, "input_accn_gin")))
    driver.execute_script('document.getElementById("accn_gin").value="{}";'.format(
        "5835540, 312233122, 187250348, 8572562, 187250362, 17737322"
    ))
    analyze_btn = find_clickable(By.ID, 'analyze_accn_gin')
    analyze_btn.click()
    fasta_msg = wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#fasta_status .text-success")))
    assert "Found: 6 FASTA Sequences" in fasta_msg.text
    print("  \033[36mPassed\033[0m")


# To test
# - AW types
# - Dissimilarity Indexes
def main():
    logged_in = False
    failed = False
    try:
        print('Testing site: {}\n'.format(host))
        helper_login('muntashir.islam96@gmail.com', 'ok')
        logged_in = True
        wait.until(EC.element_to_be_clickable((By.LINK_TEXT, "Logout")))
        test_examples()
        test_input_sequence()
        test_input_accn_gid()
        test_upload_file()
    except:
        print("  \033[31mFailed\033[0m".format())
        failed = True
    finally:
        if logged_in:
            helper_logout()
        driver.quit()
    return 1 if failed else 0


if __name__ == '__main__':
    sys.exit(main())
