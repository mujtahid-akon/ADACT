# Tests for ADACT

## Requirements
- Python3
- Selenium webdriver
  For macOS:
  ```bash
    sudo easy_install-3.7 selenium
  ```
- webdriver manager (Comment out line 3 and line 13 of `basic_test.py` to use this, optional) 
  ```bash
    pip3 install webdriver-manager
  ```
- [chromedrive](https://chromedriver.chromium.org/) matching Chrome browser version you're currently running
  (installation path must be defined at `~/.bashrc`)

## Test files
Currently there are 2 test files. Each test file take a single (optional) argument: the host name
(default is `127.0.0.1:8080`).

### `basic_test.py`
Currently tests guest login and normal login

### `new_project_test.py`
Currently tests new project page (examples and various input methods)
