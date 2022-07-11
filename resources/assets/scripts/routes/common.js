import {Solar} from '../solar';

export default {
  init() {
  },
  finalize() {
    Solar.start();
  },
};
