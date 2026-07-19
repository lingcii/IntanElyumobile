SHELL := /bin/bash

.PHONY: setup open clean

setup: install-xcodegen install-deps generate

install-xcodegen:
	@echo "Installing XcodeGen..."
	@which xcodegen > /dev/null 2>&1 || brew install xcodegen
	@echo "XcodeGen ready."

generate:
	@echo "Generating Xcode project..."
	xcodegen generate
	@echo "Generated IntanElyu.xcodeproj"

open: generate
	@open IntanElyu.xcodeproj

clean:
	@rm -rf IntanElyu.xcodeproj
	@echo "Cleaned project file."
