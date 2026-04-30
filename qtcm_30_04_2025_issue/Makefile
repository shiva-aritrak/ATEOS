include $(TOPDIR)/rules.mk
include $(INCLUDE_DIR)/package.mk
include $(INCLUDE_DIR)/kernel.mk

PKG_NAME:=qtcm
PKG_VERSION:=1.0.0
PKG_RELEASE:=$(AUTORELEASE)

PKG_MAINTAINER:=Shiva Aradi <developer.aradi@gmail.com>
PKG_LICENSE:=GNU GENERAL PUBLIC LICENSE

define Package/qtcm
	CATEGORY:=openwrt package
	SUBMENU:=Utils
	# Select package by default
	DEFAULT:=y
	TITLE:=Quectel QTCM application
	URL:=https://www.openwrt.com
	DEPENDS:=+kmod-usb-serial +kmod-usb-serial-option +kmod-usb-net +kmod-usb-net-cdc-ether +kmod-usb-net-cdc-ncm +kmod-qtcm-kernel
endef

define Package/qtcm/description
	QTCM application for 4G/5G.
endef

define KernelPackage/qtcm-kernel
  SUBMENU:=USB Support
  TITLE:=Quectel QMI WWAN driver 
  FILES:=$(PKG_BUILD_DIR)/qmi-wwan-q/src/qmi_wwan_q.ko
  DEPENDS:=@USB_SUPPORT +kmod-usb-core +kmod-usb-net +kmod-usb-wdm
endef

define KernelPackage/qtcm-kernel/description
  Quectel QMI WWAN driver for USB modems
endef

define Build/Prepare
	mkdir -p $(PKG_BUILD_DIR)
	$(CP) ./quectel-CM/src/* $(PKG_BUILD_DIR)/
	$(INSTALL_DIR) $(PKG_BUILD_DIR)/qmi-wwan-q/src
	$(CP) ./qmi-wwan-q/src/* $(PKG_BUILD_DIR)/qmi-wwan-q/src/
endef

define Build/Configure
	(cd $(PKG_BUILD_DIR); \
		autoreconf -fiv; \
		./configure \
			--target=$(GNU_TARGET_NAME) \
			--host=$(GNU_TARGET_NAME) \
			--build=$(GNU_HOST_NAME) \
			--program-prefix="" \
			--program-suffix="" \
			--prefix=/usr \
			--exec-prefix=/usr \
			--bindir=/usr/bin \
			--datadir=/usr/share \
			--includedir=/usr/include \
			--infodir=/usr/share/info \
			--libdir=/usr/lib \
			--libexecdir=/usr/lib \
			--localstatedir=/var \
			--mandir=/usr/share/man \
			--sbindir=/usr/sbin \
			--sysconfdir=/etc \
			$(DISABLE_NLS) \
			$(DISABLE_LARGEFILE) \
	);
endef

define Build/Compile
	$(MAKE) -C $(PKG_BUILD_DIR) \
		$(TARGET_CONFIGURE_OPTS) \
		CFLAGS="$(TARGET_CFLAGS)" \
		LDFLAGS="$(TARGET_LDFLAGS)"
	$(KERNEL_MAKE) M=$(PKG_BUILD_DIR)/qmi-wwan-q/src modules
endef

define Package/qtcm/install
	$(INSTALL_DIR) $(1)/etc/config
	$(INSTALL_CONF) ./quectel-CM/files/etc/config/qtcm $(1)/etc/config/qtcm

	$(INSTALL_DIR) $(1)/etc/init.d
	$(INSTALL_BIN) ./quectel-CM/files/etc/init.d/qtcm $(1)/etc/init.d/qtcm

	$(INSTALL_DIR) $(1)/sbin
	$(INSTALL_BIN) $(PKG_BUILD_DIR)/quectel-CM $(1)/sbin/
	$(INSTALL_BIN) $(PKG_BUILD_DIR)/quectel-qmi-proxy $(1)/sbin/
	$(INSTALL_BIN) $(PKG_BUILD_DIR)/quectel-mbim-proxy $(1)/sbin/
	$(INSTALL_BIN) $(PKG_BUILD_DIR)/quectel-atc-proxy $(1)/sbin/
endef

define Package/qtcm/postinst
#!/bin/sh

if [ -n "$${IPKG_INSTROOT}" ]; then
	mkdir -p "$${IPKG_INSTROOT}/etc/rc.d"
	ln -sf ../init.d/qtcm "$${IPKG_INSTROOT}/etc/rc.d/S99qtcm"
else
	uci -q get qtcm.main >/dev/null || uci set qtcm.main='qtcm'
	[ -n "$$(uci -q get qtcm.main.enabled)" ] || uci set qtcm.main.enabled='1'
	[ -n "$$(uci -q get qtcm.main.log)" ] || uci set qtcm.main.log='1'
	[ -n "$$(uci -q get qtcm.main.log_file)" ] || uci set qtcm.main.log_file='/tmp/q-cm.log'
	[ -n "$$(uci -q get qtcm.main.usbmon_log_file)" ] || uci set qtcm.main.usbmon_log_file=''
	[ -n "$$(uci -q get qtcm.main.cell_internet_mode)" ] || uci set qtcm.main.cell_internet_mode='nat'
	[ -n "$$(uci -q get qtcm.main.network_interface)" ] || uci set qtcm.main.network_interface=''
	[ -n "$$(uci -q get qtcm.main.at_port)" ] || uci set qtcm.main.at_port=''
	[ -n "$$(uci -q get qtcm.main.at_cmd)" ] || uci set qtcm.main.at_cmd=''
	[ -n "$$(uci -q get qtcm.main.pincode)" ] || uci set qtcm.main.pincode=''
	[ -n "$$(uci -q get qtcm.main.proxy_mode)" ] || uci set qtcm.main.proxy_mode=''
	[ -n "$$(uci -q get qtcm.main.pdp)" ] || uci set qtcm.main.pdp='1'
	[ -n "$$(uci -q get qtcm.main.load_qmi_wwan_q)" ] || uci set qtcm.main.load_qmi_wwan_q='0'
	[ -n "$$(uci -q get qtcm.main.qmap_mode)" ] || uci set qtcm.main.qmap_mode='1'
	[ -n "$$(uci -q get qtcm.main.mux_id)" ] || uci set qtcm.main.mux_id=''
	[ -n "$$(uci -q get qtcm.main.ip_type)" ] || uci set qtcm.main.ip_type='ipv4'
	[ -n "$$(uci -q get qtcm.main.verbose)" ] || uci set qtcm.main.verbose='0'
	[ -n "$$(uci -q get qtcm.main.no_dhcp)" ] || uci set qtcm.main.no_dhcp='0'
	[ -n "$$(uci -q get qtcm.main.auto_apn)" ] || uci set qtcm.main.auto_apn='1'
	[ -n "$$(uci -q get qtcm.main.apn)" ] || uci set qtcm.main.apn=''
	[ -n "$$(uci -q get qtcm.main.username)" ] || uci set qtcm.main.username=''
	[ -n "$$(uci -q get qtcm.main.password)" ] || uci set qtcm.main.password=''
	[ -n "$$(uci -q get qtcm.main.auth)" ] || uci set qtcm.main.auth='none'
	uci commit qtcm
	/etc/init.d/qtcm enable
fi

exit 0
endef

define Package/qtcm/prerm
#!/bin/sh

[ -n "$${IPKG_INSTROOT}" ] || /etc/init.d/qtcm disable

exit 0
endef

$(eval $(call BuildPackage,qtcm))
$(eval $(call KernelPackage,qtcm-kernel))
