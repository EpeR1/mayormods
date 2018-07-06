ALTER TABLE `diak` MODIFY `statusz` enum('felvételt nyert','jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva') COLLATE utf8_hungarian_ci NOT NULL;
ALTER TABLE `diakJogviszony` MODIFY `statusz` enum('felvételt nyert','jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva') COLLATE utf8_hungarian_ci NOT NULL;
