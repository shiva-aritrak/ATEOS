include $(TOPDIR)/rules.mk

PKG_NAME:=at-svelte
PKG_VERSION:=0.1.0
PKG_RELEASE:=1

PKG_MAINTAINER:=Aritrak Technologies
PKG_LICENSE:=Proprietary

include $(INCLUDE_DIR)/package.mk

define Package/at-svelte
  SECTION:=utils
  CATEGORY:=Utilities
  TITLE:=Aritrak Svelte web UI (port 80/443, LuCI moved to 90/91)
  DEPENDS:=+uhttpd +uhttpd-mod-ubus +rpcd
  PKGARCH:=all
endef

define Package/at-svelte/description
  Svelte based web UI served by its own uhttpd instance on port 80/443,
  talking to the router through ubus (/ubus) and the at-svelte rpcd plugin.
  LuCI keeps running on a separate instance on port 90/91.
  Building requires Node.js >= 18 and npm on the build host.
endef

define Build/Prepare
	mkdir -p $(PKG_BUILD_DIR)/ui
	tar -C ./ui --exclude=node_modules --exclude=dist -cf - . | \
		tar -C $(PKG_BUILD_DIR)/ui -xf -
endef

define Build/Configure
endef

define Build/Compile
	@command -v npm >/dev/null || { \
		echo "at-svelte: Node.js >= 18 and npm are required on the build host" >&2; \
		exit 1; }
	cd $(PKG_BUILD_DIR)/ui && \
		npm ci --no-audit --no-fund && \
		npm run build
endef

define Package/at-svelte/install
	$(INSTALL_DIR) $(1)/www-at
	$(CP) $(PKG_BUILD_DIR)/ui/dist/. $(1)/www-at/
	$(INSTALL_DIR) $(1)/etc/uci-defaults
	$(INSTALL_BIN) ./files/at-svelte.uci-defaults $(1)/etc/uci-defaults/90-at-svelte-uhttpd
	$(INSTALL_DIR) $(1)/usr/libexec/rpcd
	$(INSTALL_BIN) ./files/at-svelte.rpcd $(1)/usr/libexec/rpcd/at-svelte
	$(INSTALL_DIR) $(1)/usr/share/rpcd/acl.d
	$(INSTALL_DATA) ./files/at-svelte.acl.json $(1)/usr/share/rpcd/acl.d/at-svelte.json
endef

define Package/at-svelte/postinst
#!/bin/sh
[ -n "$${IPKG_INSTROOT}" ] && exit 0
/etc/init.d/rpcd reload
/etc/init.d/uhttpd restart
exit 0
endef

$(eval $(call BuildPackage,at-svelte))
